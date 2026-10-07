"""Instala el entorno del servicio biométrico.

Instala PyTorch CPU-only (más liviano) y luego el resto de dependencias.
Uso: python install.py
"""
from pathlib import Path
import shutil
import subprocess
import sys
import venv

root = Path(__file__).resolve().parent

if sys.version_info < (3, 11):
    raise SystemExit('Se requiere Python 3.11 o posterior.')

environment = root / 'venv'
if not environment.exists():
    print('Creando entorno virtual...')
    venv.create(environment, with_pip=True)

python = environment / ('Scripts/python.exe' if sys.platform == 'win32' else 'bin/python')

print('Instalando PyTorch CPU (puede tardar unos minutos)...')
subprocess.run([
    str(python), '-m', 'pip', 'install',
    'torch', 'torchvision',
    '--index-url', 'https://download.pytorch.org/whl/cpu',
    '--quiet',
], check=True)

print('Instalando dependencias del servicio...')
subprocess.run([
    str(python), '-m', 'pip', 'install',
    '-r', str(root / 'requirements.txt'),
    '--quiet',
], check=True)

if not (root / '.env').exists():
    shutil.copyfile(root / '.env.example', root / '.env')
    print('Archivo .env creado. Configura BIOMETRICS_TOKEN antes de iniciar.')

print()
print('✅  Entorno listo.')
print(f'▶   Iniciar servicio: "{python}" "{root / "run.py"}"')
