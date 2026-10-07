#!/usr/bin/env bash
set -euo pipefail
project_dir="$(cd -- "$(dirname -- "${BASH_SOURCE[0]}")" && pwd)"
php_bin="$(command -v php)"
# Replace only our marked line. Keep all unrelated jobs.
marker="# bigggym-scheduler:${project_dir}"
existing="$(crontab -l 2>/dev/null || true)"
job="* * * * * cd \"${project_dir}\" && \"${php_bin}\" artisan schedule:run >> /dev/null 2>&1 ${marker}"
{ printf '%s\n' "$existing" | awk -v marker="$marker" 'index($0, marker) == 0'; printf '%s\n' "$job"; } | crontab -
printf 'Programador instalado para %s con %s\n' "$project_dir" "$php_bin"
