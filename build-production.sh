#!/usr/bin/env bash

# --------------------------------------------------------------------------
# Copyright 2026 VoroDeveloper
#
# Licensed under the Apache License, Version 2.0 (the "License");
# you may not use this file except in compliance with the License.
# You may obtain a copy of the License at
#
#     http://apache.org
#
# Unless required by applicable law or agreed to in writing, software
# distributed under the License is distributed on an "AS IS" BASIS,
# WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
# See the License for the specific language governing permissions and
# limitations under the License.
# --------------------------------------------------------------------------

set -Eeuo pipefail

project_dir="$(cd -- "$(dirname -- "${BASH_SOURCE[0]}")" && pwd -P)"
current_dir="$(pwd -P)"

if [[ "$current_dir" != "$project_dir" ]]; then
    printf 'Error: run this script from the project root:\n  cd "%s" && ./build-production.sh\n' "$project_dir" >&2
    exit 1
fi

project_name="$(basename -- "$project_dir")"

if [[ "$project_name" == *-prod ]]; then
    printf 'Error: do not run this script from a directory whose name already ends in -prod.\n' >&2
    exit 1
fi

parent_dir="$(dirname -- "$project_dir")"
destination_dir="${parent_dir}/${project_name}-prod"

if [[ ! -f "${project_dir}/composer.json" || ! -f "${project_dir}/composer.lock" ]]; then
    printf 'Error: composer.json and composer.lock must exist in the project root.\n' >&2
    exit 1
fi

if ! command -v composer >/dev/null 2>&1; then
    printf 'Error: required command not found: composer\n' >&2
    exit 1
fi

if [[ -e "$destination_dir" || -L "$destination_dir" ]]; then
    printf 'Error: destination already exists; it has not been modified:\n  %s\n' "$destination_dir" >&2
    exit 1
fi

if [[ ! -x "${project_dir}/vendor/bin/phpunit" || ! -f "${project_dir}/phpunit.xml" ]]; then
    printf 'Error: PHPUnit or phpunit.xml was not found. Install the development dependencies with "composer install" and try again.\n' >&2
    exit 1
fi

printf 'Running tests before preparing the production copy...\n'
"${project_dir}/vendor/bin/phpunit" --configuration "${project_dir}/phpunit.xml"

staging_dir="$(mktemp -d "${parent_dir}/.${project_name}-prod.XXXXXX")"

cleanup() {
    if [[ -n "${staging_dir:-}" && -d "$staging_dir" ]]; then
        rm -rf -- "$staging_dir"
    fi
}
trap cleanup EXIT

for required_path in public src stats.php; do
    if [[ ! -e "${project_dir}/${required_path}" ]]; then
        printf 'Error: required production file or directory not found: %s\n' "$required_path" >&2
        exit 1
    fi
done

cp -a "${project_dir}/public" "${project_dir}/src" "${staging_dir}/"
cp -- "${project_dir}/stats.php" "${staging_dir}/"
cp -- "${project_dir}/composer.json" "${project_dir}/composer.lock" "${staging_dir}/"

composer install \
    --working-dir="$staging_dir" \
    --no-dev \
    --optimize-autoloader \
    --no-interaction \
    --prefer-dist

chmod 755 "$staging_dir"

if [[ -e "$destination_dir" || -L "$destination_dir" ]]; then
    printf 'Error: the destination appeared while preparing the production copy; it has not been overwritten:\n  %s\n' "$destination_dir" >&2
    exit 1
fi

mv -T -- "$staging_dir" "$destination_dir"
staging_dir=''

printf 'Production copy prepared successfully:\n  %s\n' "$destination_dir"
