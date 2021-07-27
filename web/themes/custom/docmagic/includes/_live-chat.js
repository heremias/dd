/* -------------------------
 * NTR popups and other js
 * -------------------------
 */
function openNTRsupport(department,survey,ref) { 
	window.open('https://na.ntrsupport.com/inquiero/anonymous2.asp?login=12552&lang=us&cat='+department+'&cob=1&surpre='+survey+'pre&sur='+survey+'post&ref='+ref,'WebAnonym','toolbar=0,location=0,status=0,menubar=0,scrollbars=0,resizable=1,width=476,height=600,top=150,left=200');
}

function submitToNTR(sessioncode) {
	entry = sessioncode.value;

    if (!window.ntrTargetWindow) {}
  	else { // has been defined
      	if (!ntrTargetWindow.closed) // still open
			ntrTargetWindow.close()
      }
	ntrTargetWindow = window.open('','WebHelp','toolbar=0,location=0,status=0,menubar=0,scrollbars=0,resizable=0,width=476,height=364,top=150,left=200');
	return true;
}

function isalpha(){	var text=document.inisession.sesion.value;	var i;	var llarg=text.length;		for(i=0;i<llarg;i++)	{			if ( ((text.charAt(i)<'0') || (text.charAt(i)>'9')) && ((text.charAt(i)<'a') || (text.charAt(i)>'z')) && ((text.charAt(i)<'A') || (text.charAt(i)>'Z')) ){			alert('Only Alphanumeric characters permitted');			return false;		}	}}

/* -------------------------
 * Live chat buttons
 * -------------------------
 */
jQuery(document).ready(function () {
  jQuery('a.live-chat-button').each(function() {
    if (jQuery(this).hasClass('live-chat-dm-customer-service')) {
      jQuery(this).attr('href', "javascript:openNTRsupport('Cust_Serv1,Cust_Serv','cs','DocMagic Customer Service');");
    }

    if (jQuery(this).hasClass('live-chat-dm-technical-support')) {
      jQuery(this).attr('href', "javascript:openNTRsupport('Tech','ts','DocMagic Technical Support');");
    }

    if (jQuery(this).hasClass('live-chat-dm-setup')) {
      jQuery(this).attr('href', "javascript:openNTRsupport('Setup','na','DocMagic Setup');");
    }
  });
});
