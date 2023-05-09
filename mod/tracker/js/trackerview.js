
function togglehistory(){
    historydiv = document.getElementById("issuehistory");
    historylink = document.getElementById("togglehistorylink");
    if (historydiv.className == "visiblediv"){
        historydiv.className = "hiddendiv";
        historylink.innerText = showhistory;
    } else {
        historydiv.className = "visiblediv";
        historylink.innerText = hidehistory;
    }
}

function toggleccs(){
    ccsdiv = document.getElementById("issueccs");
    ccslink = document.getElementById("toggleccslink");
    if (ccsdiv.className == "visiblediv"){
        ccsdiv.className = "hiddendiv";
        ccslink.innerText = showccs;
    }
    else{
        ccsdiv.className = "visiblediv";
        ccslink.innerText = hideccs;
    }
}

function toggledependancies(){
    dependanciesdiv = document.getElementById("issuedependancytrees");
    dependancieslink = document.getElementById("toggledependancieslink");
    if (dependanciesdiv.className == "visiblediv"){
        dependanciesdiv.className = "hiddendiv";
        dependancieslink.innerText = showdependancies;
    } else {
        dependanciesdiv.className = "visiblediv";
        dependancieslink.innerText = hidedependancies;
    }
}

function togglecomments(){
    commentdiv = document.getElementById("issuecomments");
    commentlink = document.getElementById("togglecommentlink");
    commentlink2 = document.getElementById("togglecommentlink2");
    if (commentdiv.className == "visiblediv comments"){
        commentdiv.className = "hiddendiv comments";
        commentlink.innerText = showcomments;
        commentlink2.innerText = showcomments;
    } else {
        commentdiv.className = "visiblediv comments";
        commentlink.innerText = hidecomments;
        commentlink2.innerText = hidecomments;
    }
}
