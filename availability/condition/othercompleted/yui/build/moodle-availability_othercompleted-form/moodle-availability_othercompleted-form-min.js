YUI.add("moodle-availability_othercompleted-form",function(e,t){M.availability_othercompleted=M.availability_othercompleted||{},M.availability_othercompleted.form=e.Object(M.core_availability.plugin),M.availability_othercompleted.form.initInner=function(e){this.datcms=e},M.availability_othercompleted.form.getNode=function(t){var n='<span class="col-form-label p-r-1"> '+M.util.get_string("title","availability_othercompleted")+"</span>"+' <span class="availability-group form-group"><label>'+'<span class="accesshide">'+M.util.get_string("label_cm","availability_othercompleted")+" </span>"+'<select class="custom-select" name="cm" title="'+M.util.get_string("label_cm","availability_othercompleted")+'">'+'<option value="0">'+M.util.get_string("choosedots","moodle")+"</option>";for(var r=0;r<this.datcms.length;r++){var i=this.datcms[r];n+='<option value="'+i.id+'">'+i.name+"</option>"}n+='</select></label> <label><span class="accesshide">'+M.util.get_string("label_completion","availability_othercompleted")+' </span><select class="custom-select" '+'name="e" title="'+M.util.get_string("label_completion","availability_othercompleted")+'">'+'<option value="1">'+M.util.get_string("option_complete","availability_othercompleted")+"</option>"+"</option>"+"</option>"+"</select></label></span>";var s=e.Node.create('<span class="form-inline">'+n+"</span>");t.cm!==undefined&&s.one("select[name=cm] > option[value="+t.cm+"]")&&s.one("select[name=cm]").set("value",""+t.cm),t.e!==undefined&&s.one("select[name=e]").set("value",""+t.e);if(!M.availability_othercompleted.form.addedEvents){M.availability_othercompleted.form.addedEvents=!0;var o=e.one(".availability-field");o.delegate("change",function(){M.core_availability.form.update()},".availability_othercompleted select")}return s},M.availability_othercompleted.form.fillValue=function(e,t){e.cm=parseInt(t.one("select[name=cm]").get("value"),10),e.e=parseInt(t.one("select[name=e]").get("value"),10)},M.availability_othercompleted.form.fillErrors=function(e,t){var n=parseInt(t.one("select[name=cm]").get("value"),10);n===0&&e.push("availability_othercompleted:error_selectcmid");var r=parseInt(t.one("select[name=e]").get("value"),10);(r===2||r===3)&&this.datcms.forEach(function(t){t.id===n&&t.completiongradeitemnumber===null&&e.push("availability_othercompleted:error_selectcmidpassfail")})}},"@VERSION@",{requires:["base","node","event","moodle-core_availability-form"]});
