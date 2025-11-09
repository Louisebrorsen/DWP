#!/usr/bin/env bash
set -euo pipefail
ROOT_DIR="$(pwd)"

# læs .env
if [[ -f .env ]]; then
  set -o allexport
  source .env
  set +o allexport
else
  echo "Mangler .env i projektroden – se opsætning i beskeden."
  exit 1
fi

# Standarder
LOCAL_DIR="${SIMPLY_LOCAL_DIR:-./public}"
REMOTE_DIR="${SIMPLY_REMOTE_DIR:-/public_html}"
HOST="${SIMPLY_HOST:?SIMPLY_HOST mangler i .env}"
USER="${SIMPLY_USER:?SIMPLY_USER mangler i .env}"
PASS="${SIMPLY_PASS:?SIMPLY_PASS mangler i .env}"

# Ekskludér ting vi aldrig vil uploade
EXCLUDES=(
  ".git"
  ".DS_Store"
  "node_modules"
  ".vscode"
  ".idea"
  "vendor"
)

# Byg --exclude-glob flags
EX_FLAGS=()
for e in "${EXCLUDES[@]}"; do
  EX_FLAGS+=( --exclude-glob "$e" )
done

# Mode: default (incremental), "dry" (incremental dry-run), "full" (force reupload everything), "full-dry" (dry run of full)
MODE="${1:-}"
case "$MODE" in
  dry)
    DRYRUN=1
    FORCE=0
    echo "▶ Dry run – viser hvad der ville blive uploaded/slettet…"
    ;;
  full)
    DRYRUN=0
    FORCE=1
    echo "▶ FULL upload – alle filer bliver gen-uploadet (overwrite) …"
    ;;
  full-dry)
    DRYRUN=1
    FORCE=1
    echo "▶ FULL dry run – viser ALT der ville blive gen-uploadet (overwrite) …"
    ;;
  *)
    DRYRUN=0
    FORCE=0
    ;;
esac

# Fælles mirror-flags (inkrementel vs. fuld upload)
MIRROR_BASE_FLAGS=(
  -R
  --delete
  --verbose
  "${EX_FLAGS[@]}"
)

if [[ $FORCE -eq 1 ]]; then
  # Force reupload af alle filer: ignorer tidsstempler og overskriv altid
  MIRROR_BASE_FLAGS+=( --overwrite --ignore-time )
else
  # Kun nye/ændrede filer
  MIRROR_BASE_FLAGS+=( --only-newer )
fi

# Tilføj dry-run hvis valgt
if [[ $DRYRUN -eq 1 ]]; then
  MIRROR_BASE_FLAGS+=( --dry-run )
fi

# Kør lftp mirror (reverse = lokal → remote)
lftp -u "$USER","$PASS" "$HOST" <<EOF
set ftp:ssl-allow true
set ssl:verify-certificate no
set net:timeout 20
set net:max-retries 2
set net:reconnect-interval-base 3
set ftp:passive-mode true
set xfer:clobber yes
set cmd:trace yes

# 1) Webrod (public)
cd "$REMOTE_DIR"
lcd "$LOCAL_DIR"
mirror ${MIRROR_BASE_FLAGS[@]} . .

# hop til projektrod lokalt
lcd "$ROOT_DIR"

# 2) Backend-mapper i konto-roden (samme niveau som /public_html)
# Gå én op fra webroden på serveren til konto-roden og sørg for mapperne findes
cd "$REMOTE_DIR"
cd ..
mkdir -p app
mkdir -p includes
mkdir -p config

# app
lcd "$ROOT_DIR/app"
cd "app"
mirror ${MIRROR_BASE_FLAGS[@]} . .

# includes
lcd "$ROOT_DIR/includes"
cd "../includes"
mirror ${MIRROR_BASE_FLAGS[@]} . .

# config
lcd "$ROOT_DIR/config"
cd "../config"
mirror ${MIRROR_BASE_FLAGS[@]} . .

bye
EOF