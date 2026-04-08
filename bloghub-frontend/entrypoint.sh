#!/bin/sh
set -e

cd /app

if [ ! -f ".env" ] && [ -f ".env.example" ]; then
  cp .env.example .env
fi

exec "$@"
