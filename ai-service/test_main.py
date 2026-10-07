import base64
import asyncio
import importlib.util
import io
import os
import sys
import unittest
from pathlib import Path
from unittest.mock import MagicMock, patch

import numpy as np
from PIL import Image
from pydantic import ValidationError
import httpx

# Test decisions without downloading models or requiring a physical camera.
sys.modules['face_recognition'] = MagicMock()
spec = importlib.util.spec_from_file_location('biometrics', Path(__file__).with_name('main.py'))
engine = importlib.util.module_from_spec(spec)
spec.loader.exec_module(engine)


class RecognitionTests(unittest.TestCase):
    def request(self, method, path, **kwargs):
        async def send():
            async with httpx.AsyncClient(transport=httpx.ASGITransport(app=engine.app), base_url='http://test') as client:
                return await client.request(method, path, **kwargs)
        return asyncio.run(send())

    def test_missing_token_rejects_requests(self):
        with patch.dict(os.environ, {'BIOMETRICS_TOKEN': 't' * 64}):
            response = self.request('GET', '/health')
        self.assertEqual(response.status_code, 401)

    def test_wrong_token_rejects_requests(self):
        with patch.dict(os.environ, {'BIOMETRICS_TOKEN': 't' * 64}):
            response = self.request('GET', '/health', headers={'Authorization': 'Bearer wrong'})
        self.assertEqual(response.status_code, 401)

    def test_token_allows_health_check(self):
        with patch.dict(os.environ, {'BIOMETRICS_TOKEN': 't' * 64}):
            response = self.request('GET', '/health', headers={'Authorization': 'Bearer ' + 't' * 64})
        self.assertEqual(response.status_code, 200)
        self.assertEqual(response.json(), {'status': 'ok'})

    def test_unconfigured_service_fails_closed(self):
        with patch.dict(os.environ, {'BIOMETRICS_TOKEN': ''}):
            response = self.request('POST', '/api/recognize', json={})
        self.assertEqual(response.status_code, 503)

    def test_vectors_must_have_128_finite_values(self):
        for vector in ([0.1], [float('nan')] * 128, [float('inf')] * 128):
            with self.assertRaises(ValidationError):
                engine.FaceRecord(id=1, vector=vector)

    def test_invalid_image_is_client_error(self):
        with self.assertRaises(engine.HTTPException) as error:
            engine.decode_image('data:image/jpeg;base64,not-an-image!')
        self.assertEqual(error.exception.status_code, 422)

    def test_large_valid_image_is_resized_and_converted_to_rgb(self):
        stream = io.BytesIO()
        Image.new('L', (1920, 1080)).save(stream, format='PNG')
        image = engine.decode_image(base64.b64encode(stream.getvalue()).decode())
        self.assertEqual(image.shape, (540, 960, 3))

    def test_multiple_faces_are_rejected(self):
        with patch.object(engine.face_recognition, 'face_locations', return_value=[(0, 100, 100, 0)] * 2):
            encoding, message = engine.encode_single_face(np.zeros((200, 200, 3)))
        self.assertIsNone(encoding)
        self.assertIn('solo una persona', message)

    def test_small_face_is_rejected(self):
        with patch.object(engine.face_recognition, 'face_locations', return_value=[(0, 50, 50, 0)]):
            encoding, message = engine.encode_single_face(np.zeros((200, 200, 3)))
        self.assertIsNone(encoding)
        self.assertIn('Acércate', message)

    def test_missing_encoding_is_handled(self):
        with patch.object(engine.face_recognition, 'face_locations', return_value=[(0, 100, 100, 0)]), patch.object(engine.face_recognition, 'face_encodings', return_value=[]):
            encoding, _ = engine.encode_single_face(np.zeros((200, 200, 3)))
        self.assertIsNone(encoding)

    def match(self, distances, ids=(1, 2)):
        faces = [engine.FaceRecord(id=identity, vector=[0.1] * 128) for identity in ids]
        with patch.object(engine.face_recognition, 'face_distance', return_value=np.array(distances)):
            return engine.find_match(faces, np.zeros(128))

    def test_clear_match_is_accepted(self):
        self.assertEqual(self.match([0.32, 0.65])['member_id'], 1)

    def test_unknown_face_is_rejected(self):
        self.assertFalse(self.match([0.55, 0.65])['match'])

    def test_ambiguous_match_is_rejected(self):
        self.assertFalse(self.match([0.32, 0.34])['match'])

    def test_duplicate_templates_do_not_hide_ambiguity(self):
        self.assertFalse(self.match([0.32, 0.32, 0.34], ids=(1, 1, 2))['match'])


if __name__ == '__main__':
    unittest.main()
