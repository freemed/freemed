<?php
	// $Id$
	// $Author$

if (freemed::user_flag(USER_DATABASE)) {
      $display_buffer .= "
        <table WIDTH=\"100%\" CLASS=\"reverse\" BORDER=\"0\" CELLSPACING=\"0\"
         CELLPADDING=\"0\" VALIGN=\"TOP\" ALIGN=\"CENTER\"><tr><td>
      ";
      $result = $sql->query ("SELECT COUNT(*) FROM patient");
      if ($result) {
        $_res   = $sql->fetch_array ($result);
        $_total = $_res[0];               // total number in db
  
        if ($_total>1)
          $display_buffer .= "
            <div ALIGN=\"CENTER\">
             <b><i>$_total ".__("Patient(s) In System")."</i></b>
            </div>
          ";
        elseif ($_total==0)
          $display_buffer .= "
            <div ALIGN=\"CENTER\">
             <b><i>".__("No Patients In System")."</i></b>
            </div>
          ";
        elseif ($_total==1)
          $display_buffer .= "
            <div ALIGN=\"CENTER\">
            <b><i>".__("One Patient In System")."</i></b>
            </div>
          ";
      } else {
        $display_buffer .= "
          <div ALIGN=\"CENTER\">
           <b><i>".__("No Patients In System")."</i></b>
          </div>
        ";
      } // if there are none...
      $display_buffer .= "
        </td></tr></table>
      "; // end table statement for bar
}

$display_buffer .= "
      <br/>
      <div ALIGN=\"CENTER\">
       <b>".__("Patients By Name")."</b>
      <br/>
      <a HREF=\"$page_name?action=find&criteria=letter&f1=A\">A</a>
      <a HREF=\"$page_name?action=find&criteria=letter&f1=B\">B</a>
      <a HREF=\"$page_name?action=find&criteria=letter&f1=C\">C</a>
      <a HREF=\"$page_name?action=find&criteria=letter&f1=D\">D</a>
      <a HREF=\"$page_name?action=find&criteria=letter&f1=E\">E</a>
  
      <a HREF=\"$page_name?action=find&criteria=letter&f1=F\">F</a>
      <a HREF=\"$page_name?action=find&criteria=letter&f1=G\">G</a>
      <a HREF=\"$page_name?action=find&criteria=letter&f1=H\">H</a>
      <a HREF=\"$page_name?action=find&criteria=letter&f1=I\">I</a>
      <a HREF=\"$page_name?action=find&criteria=letter&f1=J\">J</a>
  
      <a HREF=\"$page_name?action=find&criteria=letter&f1=K\">K</a>
      <a HREF=\"$page_name?action=find&criteria=letter&f1=L\">L</a>
      <a HREF=\"$page_name?action=find&criteria=letter&f1=M\">M</a>
      <br/>
      <a HREF=\"$page_name?action=find&criteria=letter&f1=N\">N</a>
      <a HREF=\"$page_name?action=find&criteria=letter&f1=O\">O</a>
      <a HREF=\"$page_name?action=find&criteria=letter&f1=P\">P</a>
      <a HREF=\"$page_name?action=find&criteria=letter&f1=Q\">Q</a>
      <a HREF=\"$page_name?action=find&criteria=letter&f1=R\">R</a>
  
      <a HREF=\"$page_name?action=find&criteria=letter&f1=S\">S</a>
      <a HREF=\"$page_name?action=find&criteria=letter&f1=T\">T</a>
      <a HREF=\"$page_name?action=find&criteria=letter&f1=U\">U</a>
      <a HREF=\"$page_name?action=find&criteria=letter&f1=V\">V</a>
      <a HREF=\"$page_name?action=find&criteria=letter&f1=W\">W</a>
  
      <a HREF=\"$page_name?action=find&criteria=letter&f1=X\">X</a>
      <a HREF=\"$page_name?action=find&criteria=letter&f1=Y\">Y</a>
      <a HREF=\"$page_name?action=find&criteria=letter&f1=Z\">Z</a>

      <p/>

      <form ACTION=\"$page_name\" METHOD=\"POST\">
       <b>".__("Patients Field Search")."</b>
      <br/>
      <input TYPE=\"HIDDEN\" NAME=\"action\" VALUE=\"find\"/>
      <input TYPE=\"HIDDEN\" NAME=\"criteria\" VALUE=\"contains\"/>
      ".html_form::select_widget(
        'f1',
	array(
          __("Last Name") => 'ptlname',
	  __("First Name") => 'ptfname',
	  __("Date of Birth") => 'ptdob',
	  __("Internal Practice ID") => 'ptid',
	  __("City") => 'ptcity',
	  __("State") => 'ptstate',
	  __("Zip") => 'ptzip',
	  __("Home Phone") => 'pthphone',
	  __("Work Phone") => 'ptwphone',
	  __("Email Address") => 'ptemail',
	  __("Social Security Number") => 'ptssn',
	  __("Driver's License") => 'ptdmv'
	)
      )."	
      <i><small>".__("contains")."</small></i>
      ".html_form::text_widget('f2', 15, 30)."
      <input class=\"button\" TYPE=\"SUBMIT\" VALUE=\"".__("Search")."\">
      </form>
      <p/>

      <form ACTION=\"$page_name\" METHOD=\"POST\">
      <input TYPE=\"HIDDEN\" NAME=\"action\" VALUE=\"find\"/>
      <input TYPE=\"HIDDEN\" NAME=\"criteria\" VALUE=\"soundex\"/>
      <b>".__("Soundalike Search")."</b><br/>
      ".html_form::select_widget(
        "f1",
	array(
          __("Last Name") => 'ptlname',
	  __("First Name") => 'ptfname'
	)
      )."
        <i><small>".__("sounds like")."</small></i>
      ".html_form::text_widget('f2', 15, 30)."
      <input class=\"button\" TYPE=\"SUBMIT\" VALUE=\"".__("Search")."\">
      </form>
      <p/>
      </div>

      ".template::link_bar(array(
      		__("Show All Patients") =>
      		"$page_name?action=find&criteria=all&f1=",

       		__("Add Patient") =>
      		"$page_name?action=addform",

      		__("Call In Menu") =>
      		"call-in.php",

		__("Return to Main Menu") =>
		"main.php"
      ))."

      <p/> 
";

?>


