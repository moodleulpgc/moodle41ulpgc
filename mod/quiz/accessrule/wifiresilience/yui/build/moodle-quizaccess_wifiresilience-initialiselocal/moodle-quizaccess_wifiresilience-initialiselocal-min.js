YUI.add("moodle-quizaccess_wifiresilience-initialiselocal",(function(Y,NAME){M.quizaccess_wifiresilience=M.quizaccess_wifiresilience||{},M.quizaccess_wifiresilience.initialiselocal={delete_indb_record:function(key){var confirmdelete;confirm(M.util.get_string("localconfirmdeletelocal","quizaccess_wifiresilience",key))&&(responses_store.removeItem(key),document.getElementById("indb_row_"+key).style.display="none")},delete_localstorage_record:function(key){var confirmdelete;confirm(M.util.get_string("localconfirmdeletestatus","quizaccess_wifiresilience",key))&&(status_store.removeItem(key),document.getElementById("localstorage_row_"+key).style.display="none")},init:function(startwithkey){function quizaccess_wifiresilience_create_rows(tableid,rownumber,data){var table,row=document.getElementById(tableid).insertRow(rownumber),cell1,cell2,cell3,cell4;row.id="indb_row_"+data.key,row.insertCell(0).innerHTML=data.key,row.insertCell(1).innerHTML=data.key,row.insertCell(2).innerHTML=data.key,row.insertCell(3).innerHTML='<a href="#" id="download_indb_ls_'+rownumber+'">'+M.util.get_string("download","quizaccess_wifiresilience")+"</a>";var dlink_element=document.getElementById("download_indb_ls_"+rownumber),blob=new Blob([data.responses],{type:"octet/stream"}),url=window.URL.createObjectURL(blob),cell5;dlink_element.setAttribute("href",url),dlink_element.setAttribute("download",data.key+".eth"),row.insertCell(4).innerHTML='<a href="#" id="delete_indb_ls_'+rownumber+'" onclick="M.quizaccess_wifiresilience.initialiselocal.delete_indb_record(\''+data.key+"')\">"+M.util.get_string("delete","quizaccess_wifiresilience")+"</a>"}function quizaccess_wifiresilience_localstorage_create_rows(tableid,rownumber,data){var table,row=document.getElementById(tableid).insertRow(rownumber),cell1,cell2,cell3,cell4;row.id="localstorage_row_"+data.key,row.insertCell(0).innerHTML=data.key,row.insertCell(1).innerHTML=data.key,row.insertCell(2).innerHTML=data.key,row.insertCell(3).innerHTML='<a href="#" id="download_localstorage_ls_'+rownumber+'">'+M.util.get_string("download","quizaccess_wifiresilience")+"</a>";var dlink_element=document.getElementById("download_localstorage_ls_"+rownumber),blob=new Blob([data.responses],{type:"octet/stream"}),url=window.URL.createObjectURL(blob),cell5;dlink_element.setAttribute("href",url),dlink_element.setAttribute("download",data.key+".eth"),row.insertCell(4).innerHTML='<a href="#" id="delete_localstorage_ls_'+rownumber+'" onclick="M.quizaccess_wifiresilience.initialiselocal.delete_localstorage_record(\''+data.key+"')\">"+M.util.get_string("delete","quizaccess_wifiresilience")+"</a>"}responses_store=localforage.createInstance({name:"Wifiresilience-exams-responses"}),status_store=localforage.createInstance({name:"Wifiresilience-exams-question-status"}),responses_store.startsWith(startwithkey).then((function(results){var localforagedata={},row=0,foundx=0;for(var ldbindex in results)foundx=1,quizaccess_wifiresilience_create_rows("quizaccess_wifiresilience-indexeddb-table",++row,localforagedata={key:ldbindex,responses:results[ldbindex]});if(0==foundx){var table,row,cell=(row=document.getElementById("quizaccess_wifiresilience-indexeddb-table").insertRow(1)).insertCell(0);cell.innerHTML=M.util.get_string("localnorecordsfound","quizaccess_wifiresilience"),cell.colSpan=5}})),status_store.startsWith(startwithkey).then((function(results){var localforagedata={},row=0,found=0;for(var ldbindex in results)found=1,quizaccess_wifiresilience_localstorage_create_rows("quizaccess_wifiresilience-localstorage-table",++row,localforagedata={key:ldbindex,responses:results[ldbindex]});if(0==found){var table,row,cell=(row=document.getElementById("quizaccess_wifiresilience-localstorage-table").insertRow(1)).insertCell(0);cell.innerHTML=M.util.get_string("localnorecordsfound","quizaccess_wifiresilience"),cell.colSpan=5}}))}}}),"@VERSION@",{requires:["base","node","event","event-valuechange","node-event-delegate","io-form","json"]});