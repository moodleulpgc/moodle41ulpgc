YUI.add("moodle-quizaccess_wifiresilience-watching",(function(Y,NAME){M.quizaccess_wifiresilience=M.quizaccess_wifiresilience||{},M.quizaccess_wifiresilience.watching={SELECTORS:{QUIZ_FORM:"#responseform"},init:function(watchlist){var open;(watchlist=watchlist.replace(/\\\//g,"/"),this.form=Y.one(this.SELECTORS.QUIZ_FORM),this.form)?(quizaccess_wifiresilience_progress_step=9,$("#quizaccess_wifiresilience_result").html(M.util.get_string("loadingstep9","quizaccess_wifiresilience")),Y.log("Watching Live Scripts/XHR requests Initialised.(Only Initialised if Watch List is filled in WIFI-Config in admin pages)","debug","[Wifiresilience-SW] Live Watching"),wifi_xhr_args="",open=XMLHttpRequest.prototype.open,XMLHttpRequest.prototype.open=function(ev){wifi_set_xhr_args(arguments),this.addEventListener("readystatechange",(function(e){var wifi_args=wifi_get_xhr_args(),whatlist;if(-1!==wifi_get_watch_list().indexOf(wifi_args[1])){var livewatchel=document.querySelector("#quizaccess_wifiresilience_hidden_livewatch_status");4==this.readyState&&(200==this.status?(livewatchel.value=1,M.quizaccess_wifiresilience.autosave.livewatch=!0,Y.log("Intercepted Live Watch Script with status: "+this.status+". Timer is running normal.","debug","[Wifiresilience-SW] Live Watching")):(livewatchel.value=0,M.quizaccess_wifiresilience.autosave.livewatch=!1,Y.log("Intercepted Live Watch Script with status: "+this.status+". Stop Timer now until Server/Internet is responding.","debug","[Wifiresilience-SW] Live Watching")))}}),!1),open.apply(this,arguments)},quizaccess_wifiresilience_progress.animate({width:9*examviewportmaxwidth/10+"px"})):Y.log("No response form found. Why did you try to set up download?","debug","[Wifiresilience-SW] Live Watching");function wifi_get_xhr_args(){return wifi_xhr_args}function wifi_set_xhr_args(val){wifi_xhr_args=val}function wifi_get_watch_list(){return watchlist}}}}),"@VERSION@",{requires:["base","node","event","event-valuechange","node-event-delegate","io-form","json","core_question_engine","mod_quiz"]});