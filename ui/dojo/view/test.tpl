{* Smarty *}
<html>
<head>
        <!-- <script type="text/javascript" src="{$base_uri}/lib/dojo/dojo.js"></script> -->
        <script type="text/javascript">
                dojo.require("dojo.widget.TabContainer");
//              dojo.require("dojo.widget.Tooltip");
                dojo.require("dojo.widget.LinkPane");
                dojo.require("dojo.widget.ContentPane");
                dojo.require("dojo.widget.Button");
        </script>
</head>
<body>
	<div>
	<dojo:TabContainer id="mainTabContainer" style="width: 80%; height: 80%;">
		<dojo:ContentPane id="patient" label="Patient Information" href="{$base_uri}/controller.php/dojo/test?page=patient_information_page">
		</dojo:ContentPane>
		<dojo:ContentPane id="misc" label="Misc Information">
			<button dojoType="Button">I'm a another button</button>
		</dojo:ContentPane>
	</dojo:TabContainer>
	</div>
<!--
Hello afterwards.

<br/>

Quick form
<form method="post">
	<input name="data" type="submit" />
</form>
-->

</body>
</html>
