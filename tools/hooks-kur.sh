#!/usr/bin/env bash
# Repo kökünden bir kez çalıştırın (yeni makine/klon): otomatik sürüm hook'unu etkinleştirir.
cd "$(dirname "$0")/.." && git config core.hooksPath .githooks && chmod +x .githooks/post-commit && echo "Hook etkin."
