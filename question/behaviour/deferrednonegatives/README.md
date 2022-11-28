# Deferred NO negatives question behaviour

This is a question behaviour for Moodle Quiz that forces all questions to 
be graded with only positive fractional grades using deferred feedback mode. 
If the question marking is negative then it is discarded and set to cero (0). 

## What does this plugin do

Negative partial grading will take place within a question (e.g. if several 
answers are allowed), but each question can have only cero (0) or positive fractional 
grade. Negative marks are not carried to the whole quiz grade. 
It will work with a variety of question types that normally produce negative 
credit for wrong responses.

Works in a way similar to qbehaviour_deferredallnothing but only negative 
partial grades are discarded. Positive fractional grades are possible and count 
towards final grade. 

## Installation

### Install from the plugins database

Install from the Moodle plugins database https://moodle.org/plugins/qbehaviour_deferrednonegatives
in the normal way.

### Install using git

Or you can install using git. Type this commands in the root of your Moodle install

    git clone https://github.com/moodleulpgc/moodle-qbehaviour_deferrednonegatives question/behaviour/deferrednonegatives
    echo /question/behaviour/deferrednonegatives/ >> .git/info/exclude

Then run the moodle update process
Site administration > Notifications

### Setup

Once this plugin is installed, if you have a 'Record audio' question in
a quiz (or similar) that is set to use 'Immediate feedback' or
'Interactive with multiple tries', then rather than having to be
manually graded by the teacher, the question becomes self-assessed.

That is, once they have submitted, students can rate their submission on a scale
from one to five stars, with an optional comment.

## Track progress of any current and future developments ##
https://github.com/moodleulpgc/moodle-qbehaviour_deferrednonegatives

## Contributing ##
This plugin is based on qbehaviour_deferredallnothing by Daniel Thies 2015. <br />
All original files are copyright Enrique Castro @ULPGC and are licensed under the included GPL 3.

Any type of contribution, suggestions, feature request is welcome. 
Please create an issue in GitHub to discuss before doing a pull request.
