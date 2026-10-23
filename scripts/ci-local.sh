#!/usr/bin/env bash
# Local CI. GitHub workflows are disabled on this repo, so every change runs this and pastes the summary into the
# commit or PR. Each cell is PHP:Laravel:Filament. In a throwaway clone of HEAD, composer.json is narrowed to that
# Filament major and the Testbench for that Laravel major (^10 for 12, ^11 for 13), then composer update,
# Pint (once, on the first cell that resolves), PHPStan and Pest. Every PHP version, Laravel major and Filament major
# appears at least twice. Laravel 11 is not supported: every 11.x release has open security advisories, so Composer
# refuses to install it.
#   scripts/ci-local.sh
#   CELLS="8.5:13:5" scripts/ci-local.sh
# Finds php8.x on PATH, or Homebrew's php@8.x. Tests committed code (HEAD) only.
set -uo pipefail
cd "$(dirname "$0")/.."
REPO=$(pwd)

CELLS=${CELLS:-"8.2:12:4 8.2:12:5:8 8.3:12:5 8.3:13:4:8 8.3:13:5 8.4:12:4 8.4:13:5 8.5:12:5 8.5:13:4 8.5:13:5"}
RESULTS=(); FAILED=0; LINTED=0
step() { local name=$1; shift; printf '\n== %s\n' "$name"; if "$@"; then RESULTS+=("pass  $name"); else RESULTS+=("FAIL  $name"); FAILED=1; fi; }

php_bin() {
  local v=$1 p
  for p in "$(command -v "php$v" 2>/dev/null)" "$(brew --prefix 2>/dev/null)/opt/php@$v/bin/php"; do
    [ -n "$p" ] && [ -x "$p" ] && "$p" -r 'exit(PHP_MAJOR_VERSION.".".PHP_MINOR_VERSION === $argv[1] ? 0 : 1);' "$v" && { echo "$p"; return; }
  done
  return 1
}

resolved() { # dir package -> installed version
  (cd "$1" && "$PHP" "$COMPOSER" show "$2" 2>/dev/null | awk '/^versions/ {print $NF}')
}

COMPOSER=$(command -v composer)
SHIM=$(mktemp -d); WORK=$(mktemp -d); trap 'rm -rf "$SHIM" "$WORK"' EXIT
BASE_PATH=$PATH

for cell in $CELLS; do
  IFS=: read -r v l f g <<< "$cell"
  TAG="PHP $v Laravel $l Filament $f${g:+ glorand $g}"
  GL=${g:+ \"glorand/laravel-model-settings:^$g.0\"}
  PHP=$(php_bin "$v") || { RESULTS+=("FAIL  $TAG: PHP $v not found"); FAILED=1; continue; }
  # vendor/bin scripts start with "#!/usr/bin/env php": put this version first on PATH so they don't run on the default PHP.
  ln -sf "$PHP" "$SHIM/php"; export PATH="$SHIM:$BASE_PATH"
  case "$l" in 12) TB='^10.0' ;; *) TB='^11.0' ;; esac
  DIR="$WORK/l$l-f$f-php$v"
  git clone --quiet --no-hardlinks "$REPO" "$DIR" && git -C "$DIR" checkout --quiet --detach "$(git rev-parse HEAD)"
  step "$TAG composer update" sh -c "\"$PHP\" \"$COMPOSER\" -d \"$DIR\" require --no-update --no-interaction 'filament/filament:^$f.0' && \"$PHP\" \"$COMPOSER\" -d \"$DIR\" require --no-update --no-interaction --dev 'orchestra/testbench:$TB'$GL && \"$PHP\" \"$COMPOSER\" -d \"$DIR\" update -W --no-interaction --no-progress --prefer-dist -q"
  RL=$(resolved "$DIR" laravel/framework); RF=$(resolved "$DIR" filament/filament)
  step "$TAG resolved Laravel ${RL:-nothing}, Filament ${RF:-nothing}" sh -c "case '$RL' in v$l.*) ;; *) exit 1 ;; esac; case '$RF' in v$f.*) exit 0 ;; *) exit 1 ;; esac"
  if [ "$LINTED" = "0" ] && [ -n "$RL" ]; then
    step "$TAG pint --test" "$PHP" "$COMPOSER" -d "$DIR" lint
    LINTED=1
  fi
  step "$TAG phpstan" "$PHP" "$COMPOSER" -d "$DIR" analyse
  step "$TAG pest" "$PHP" "$COMPOSER" -d "$DIR" test
  rm -rf "$DIR"
done

printf '\n== Summary (%s, %s)\n' "$(git rev-parse --short HEAD)" "$(date '+%Y-%m-%d %H:%M')"
printf '%s\n' "${RESULTS[@]}"
exit $FAILED
