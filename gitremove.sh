#!/bin/bash
# Remove the submodule entry from .git/config
git submodule deinit -f $1

# Remove the submodule directory from the superproject's .git/modules directory
rm -rf .git/modules/$1

# Remove the entry in .gitmodules and remove the submodule directory located at path/to/submodule
git rm -rf $1
