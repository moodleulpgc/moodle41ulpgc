## MOODLE-ATTO_pumukitmedia DEVELOPER GUIDE

How to generate and update the build for Atto Plugin

#### Step 1: Install dependencies for the work.
```
sudo apt-get install npm
sudo apt-get install python-software-properties python g++ make
sudo apt-get update
sudo apt-get install nodejs
sudo ln -s /usr/bin/nodejs /usr/local/bin/node
sudo npm install shifter@0.4.6 -g
```

#### Step 2: Build plugin on code change ( reports errors, recommended for develop )

```
shifter --watch
```

#### Step 3: Build to upload code and generate the new version.

```
shifter
```

#### Step 4: Install the plugin on Moodle

First generates zip of plugin.
```
zip -r moodle-atto_pumukitmedia.zip  moodle-atto_pumukitmedia/ -x "moodle-atto_pumukitmedia/.git/*" -x "moodle-atto_pumukitmedia/.github/*" -x "moodle-atto_pumukitmedia/.gitignore" 
```

References:

* https://docs.moodle.org/dev/YUI/Shifter
* https://docs.moodle.org/dev/Atto
* https://docs.moodle.org/dev/Grunt
* https://github.com/justinhunt/moodle-atto_newtemplate/

