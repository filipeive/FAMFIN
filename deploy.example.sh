#!/bin/bash
# ╔═══════════════════════════════════════════════════════╗
# ║  FAMFIN — One-Click Deploy Script (Example Template)  ║
# ╚═══════════════════════════════════════════════════════╝

set -e

# Configuração do Servidor
REMOTE_USER="seu-usuario"
REMOTE_HOST="seu-ip-ou-dominio"
REMOTE_PATH="/var/www/html/famfin"
BRANCH="main"
REPO_ORIGIN="origin"

echo "Deploying ${BRANCH} to ${REMOTE_USER}@${REMOTE_HOST}:${REMOTE_PATH}..."

# 1. Push para repositório remoto
git push "$REPO_ORIGIN" "$BRANCH"

# 2. Pull no servidor de produção
ssh -o StrictHostKeyChecking=no "${REMOTE_USER}@${REMOTE_HOST}" \
    "cd ${REMOTE_PATH} && sudo git pull origin ${BRANCH}"

# 3. Dependências e migrações
ssh -o StrictHostKeyChecking=no "${REMOTE_USER}@${REMOTE_HOST}" \
    "cd ${REMOTE_PATH} && composer install --no-dev --optimize-autoloader && sudo php artisan optimize:clear && sudo php artisan migrate --force"

# 4. Permissões
ssh -o StrictHostKeyChecking=no "${REMOTE_USER}@${REMOTE_HOST}" \
    "cd ${REMOTE_PATH} && sudo chown -R www-data:www-data storage bootstrap/cache && sudo chmod -R 775 storage bootstrap/cache"

echo "Deploy finalizado com sucesso!"
