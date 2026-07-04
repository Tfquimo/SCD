#!/usr/bin/env bash
# ==========================================================
# Script de Build para o Render.com
# Executado automaticamente em cada deploy
# ==========================================================
set -e  # Sai imediatamente se algum comando falhar

echo "==> [1/6] Instalando dependências PHP (sem dev)..."
composer install --no-dev --optimize-autoloader

echo "==> [2/6] Instalando dependências Node.js..."
npm ci

echo "==> [3/6] Compilando assets (Vite + Tailwind)..."
npm run build

echo "==> [4/6] Otimizando configuração do Laravel..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo "==> [5/6] Criando link simbólico para storage..."
php artisan storage:link || true

echo "==> [6/6] Executando migrations..."
php artisan migrate --force

echo ""
echo "✅ Build concluído com sucesso!"
