#!/bin/sh
# Crea dist/lu3g-login-brand.zip (cartella lu3g-login-brand/ all'interno), il pacchetto che WordPress installa e aggiorna.
# Esclude i file che servono solo allo sviluppo (git, script, automazioni).
set -e
cd "$(dirname "$0")/.."
rm -rf dist && mkdir -p dist/lu3g-login-brand
cp -R lu3g-login-brand.php readme.txt assets includes dist/lu3g-login-brand/
(cd dist && zip -rq lu3g-login-brand.zip lu3g-login-brand -x '*.DS_Store')
rm -rf dist/lu3g-login-brand
echo "dist/lu3g-login-brand.zip"
