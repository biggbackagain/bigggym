"""Run the optional recognition service without Docker."""
import os
from pathlib import Path

from dotenv import load_dotenv
import uvicorn

if __name__ == '__main__':
    root = Path(__file__).resolve().parent
    load_dotenv(root / '.env')
    if len(os.environ.get('BIOMETRICS_TOKEN', '')) < 32:
        raise SystemExit('Configura BIOMETRICS_TOKEN (32 caracteres mínimo) en ai-service/.env.')
    os.chdir(root)
    uvicorn.run('main:app', host=os.getenv('AI_HOST', '127.0.0.1'),
                port=int(os.getenv('AI_PORT', '8005')), workers=1,
                reload=False, access_log=False, limit_concurrency=4)
