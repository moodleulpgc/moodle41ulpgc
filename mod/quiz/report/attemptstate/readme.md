# Manage quiz attempts state report

This 'report' is actually a tool like the standard Response quiz report, 
but which lets Staff to manage the state of quiz attempts generated by users. 
Teachers can finalize an abandoned attempt in order to get it automatically graded.
Or can generate a new attempt based on an interrupted one on behalf of a student 
(i.e. even if quiz do not allow new attempts bases on previous one).
Optionaly, teachers can revert a finished or otherwise closed attempt to 
In progress state, to allow to answer those not yer answereed questions.


## Installation and set-up

This plugin should be compatible with Moodle 3.9+

### Install using git

Or you can install using git. Type this commands in the root of your Moodle install

    git clone https://github.com/moodleou/moodle-quiz_attemptstate.git mod/quiz/report/attemptstate
    echo '/mod/quiz/report/attemptstate/' >> .git/info/exclude
    
Then run the moodle update process
Site administration > Notifications
