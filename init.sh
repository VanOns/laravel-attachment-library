#!/bin/bash

# This script is used to initialize the environment for the project.

set -u

# Variables

debug=0
name=""
slug=""
description=""
dependabot=0

# General functions

slugify() {
  echo "$1" | iconv -c -t ascii//TRANSLIT | sed -E 's/[~^]+//g' | sed -E 's/[^a-zA-Z0-9]+/-/g' | sed -E 's/^-+|-+$//g' | tr A-Z a-z
}

yesno() {
  while true; do
    read -p "$1 (Y/n) " answer
    case $answer in
      [Nn]* ) echo -e "--> $3\n"; return 1;;
      * ) echo -e "--> $2\n"; return 0;;
    esac
  done
}

# Core functions

start() {
  clear

  while [ $# -gt 0 ] ; do
    case $1 in
      -d | --debug)
        debug=1
        echo -e "--> Debug mode enabled <--\n"
        ;;
    esac
    shift
  done
}

intro() {
  echo " ▄               ▄  ▄▄▄▄▄▄▄▄▄▄▄     ▄▄▄▄▄▄▄▄▄▄▄  ▄▄▄▄▄▄▄▄▄▄▄  ▄▄▄▄▄▄▄▄▄▄▄  ▄▄        ▄     ▄▄▄▄▄▄▄▄▄▄▄  ▄▄▄▄▄▄▄▄▄▄▄  ▄         ▄  ▄▄▄▄▄▄▄▄▄▄▄  ▄▄▄▄▄▄▄▄▄▄▄  ▄▄▄▄▄▄▄▄▄▄▄ "
  sleep 0.1
  echo "▐░▌             ▐░▌▐░░░░░░░░░░░▌   ▐░░░░░░░░░░░▌▐░░░░░░░░░░░▌▐░░░░░░░░░░░▌▐░░▌      ▐░▌   ▐░░░░░░░░░░░▌▐░░░░░░░░░░░▌▐░▌       ▐░▌▐░░░░░░░░░░░▌▐░░░░░░░░░░░▌▐░░░░░░░░░░░▌"
  sleep 0.1
  echo " ▐░▌           ▐░▌ ▐░█▀▀▀▀▀▀▀█░▌   ▐░█▀▀▀▀▀▀▀█░▌▐░█▀▀▀▀▀▀▀█░▌▐░█▀▀▀▀▀▀▀▀▀ ▐░▌░▌     ▐░▌   ▐░█▀▀▀▀▀▀▀▀▀ ▐░█▀▀▀▀▀▀▀█░▌▐░▌       ▐░▌▐░█▀▀▀▀▀▀▀█░▌▐░█▀▀▀▀▀▀▀▀▀ ▐░█▀▀▀▀▀▀▀▀▀ "
  sleep 0.1
  echo "  ▐░▌         ▐░▌  ▐░▌       ▐░▌   ▐░▌       ▐░▌▐░▌       ▐░▌▐░▌          ▐░▌▐░▌    ▐░▌   ▐░▌          ▐░▌       ▐░▌▐░▌       ▐░▌▐░▌       ▐░▌▐░▌          ▐░▌          "
  sleep 0.1
  echo "   ▐░▌       ▐░▌   ▐░▌       ▐░▌   ▐░▌       ▐░▌▐░█▄▄▄▄▄▄▄█░▌▐░█▄▄▄▄▄▄▄▄▄ ▐░▌ ▐░▌   ▐░▌   ▐░█▄▄▄▄▄▄▄▄▄ ▐░▌       ▐░▌▐░▌       ▐░▌▐░█▄▄▄▄▄▄▄█░▌▐░▌          ▐░█▄▄▄▄▄▄▄▄▄ "
  sleep 0.1
  echo "    ▐░▌     ▐░▌    ▐░▌       ▐░▌   ▐░▌       ▐░▌▐░░░░░░░░░░░▌▐░░░░░░░░░░░▌▐░▌  ▐░▌  ▐░▌   ▐░░░░░░░░░░░▌▐░▌       ▐░▌▐░▌       ▐░▌▐░░░░░░░░░░░▌▐░▌          ▐░░░░░░░░░░░▌"
  sleep 0.1
  echo "     ▐░▌   ▐░▌     ▐░▌       ▐░▌   ▐░▌       ▐░▌▐░█▀▀▀▀▀▀▀▀▀ ▐░█▀▀▀▀▀▀▀▀▀ ▐░▌   ▐░▌ ▐░▌    ▀▀▀▀▀▀▀▀▀█░▌▐░▌       ▐░▌▐░▌       ▐░▌▐░█▀▀▀▀█░█▀▀ ▐░▌          ▐░█▀▀▀▀▀▀▀▀▀ "
  sleep 0.1
  echo "      ▐░▌ ▐░▌      ▐░▌       ▐░▌   ▐░▌       ▐░▌▐░▌          ▐░▌          ▐░▌    ▐░▌▐░▌             ▐░▌▐░▌       ▐░▌▐░▌       ▐░▌▐░▌     ▐░▌  ▐░▌          ▐░▌          "
  sleep 0.1
  echo "       ▐░▐░▌       ▐░█▄▄▄▄▄▄▄█░▌   ▐░█▄▄▄▄▄▄▄█░▌▐░▌          ▐░█▄▄▄▄▄▄▄▄▄ ▐░▌     ▐░▐░▌    ▄▄▄▄▄▄▄▄▄█░▌▐░█▄▄▄▄▄▄▄█░▌▐░█▄▄▄▄▄▄▄█░▌▐░▌      ▐░▌ ▐░█▄▄▄▄▄▄▄▄▄ ▐░█▄▄▄▄▄▄▄▄▄ "
  sleep 0.1
  echo "        ▐░▌        ▐░░░░░░░░░░░▌   ▐░░░░░░░░░░░▌▐░▌          ▐░░░░░░░░░░░▌▐░▌      ▐░░▌   ▐░░░░░░░░░░░▌▐░░░░░░░░░░░▌▐░░░░░░░░░░░▌▐░▌       ▐░▌▐░░░░░░░░░░░▌▐░░░░░░░░░░░▌"
  sleep 0.1
  echo "         ▀          ▀▀▀▀▀▀▀▀▀▀▀     ▀▀▀▀▀▀▀▀▀▀▀  ▀            ▀▀▀▀▀▀▀▀▀▀▀  ▀        ▀▀     ▀▀▀▀▀▀▀▀▀▀▀  ▀▀▀▀▀▀▀▀▀▀▀  ▀▀▀▀▀▀▀▀▀▀▀  ▀         ▀  ▀▀▀▀▀▀▀▀▀▀▀  ▀▀▀▀▀▀▀▀▀▀▀ "

  echo -e "\nWelcome to the VO Open Source project initializer!\n"
  echo -e "To initialize the project you will be asked some questions."
  echo -e "Your answers will be used to generate all files and folders.\n"
  echo -e "Press any key to continue..."
  read -n 1 -s
}

general() {
  echo -e ""
  while [[ $name = "" ]]; do
    read -p "Project name (required): " name
  done

  default_slug=$(slugify "$name")

  echo -e "\nGenerated slug: $default_slug\n"

  use_default_slug=1
  if ! yesno "Do you want to use this slug?" "Using generated slug" "Using custom slug"; then
    use_default_slug=0
  fi

  if [[ $use_default_slug = 0 ]]; then
    while [[ $slug = "" ]]; do
      read -p "Project slug (required): " slug
    done
    echo -e ""
  else
    slug=$default_slug
  fi

  while [[ $description = "" ]]; do
    read -p "Project description (required): " description
  done

  echo -e ""
  yesno "The repository is equipped with the MIT license. Do you want to use this license?" "Using MIT license" "You have to change the license manually. Please choose a license (take a look at https://choosealicense.com/) and make sure to update all files referencing the license, as well as the repository settings."

  if yesno "Do you want a dependabot config file for automatic dependabot updates?" "Adding dependabot.yml" "Skipping dependabot.yml"; then
    dependabot=1
  fi
}

backup() {
  echo -e "Backing up all files to .backup folder..."

  if [[ $debug = 1 ]]; then
    echo -e "[DEBUG] Skipping back up...\n"
  else
    mkdir -p .backup
    cp -aR template .backup/
    cp -a .gitignore init.sh LICENSE.md README.md .backup/
  fi
}

replace() {
  echo -e "Preparing template files..."

  if [[ $debug = 1 ]]; then
    echo -e "[DEBUG] Skipping search/replace...\n"
  else
    find . -path './template/*' -type f -exec sed -i '' -e 's/\[project-name\]/'"$name"'/g' {} \;
    find . -path './template/*' -type f -exec sed -i '' -e 's/\[project-slug\]/'"$slug"'/g' {} \;
    find . -path './template/*' -type f -exec sed -i '' -e 's/\[project-description\]/'"$description"'/g' {} \;
  fi
}

move() {
  echo -e "Moving template files..."

  if [[ $debug = 1 ]]; then
    echo -e "[DEBUG] Skipping move...\n"
  else
    cp -aR template/. .
    rm -rf template/

    if [[ $dependabot = 0 ]]; then
      rm .github/dependabot.yml
    fi
  fi
}

finish() {
  echo -e "Done! This file will now be deleted..."

  if [[ $debug = 1 ]]; then
    echo -e "[DEBUG] Skipping deletion...\n"
  else
    rm init.sh
  fi
}

start "$@"
intro
general
backup
replace
move
finish

echo -e "Goodbye!"
exit 0
