<?xml version="1.0" encoding="UTF-8" ?>
<!--

	$Id$
	Original from https://bugs.eclipse.org/bugs/attachment.cgi?id=54746

-->
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:n3="http://www.w3.org/1999/xhtml" xmlns:n1="urn:hl7-org:v3" xmlns:n2="urn:hl7-org:v3/meta/voc" xmlns:voc="urn:hl7-org:v3/voc" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
<xsl:output method="html" indent="yes" version="4.01" encoding="ISO-8859-1" doctype-public="-//W3C//DTD HTML 4.01//EN"/>

<!--<xsl:key name="ObjectIDKey" match="//n1:id" use="n1:root"/>-->


<!-- CDA document -->
<xsl:variable name="tableWidth">50%</xsl:variable>

<xsl:variable name="title">
	<xsl:choose>
		<xsl:when test="/n1:ClinicalDocument/n1:title">
			<xsl:value-of select="/n1:ClinicalDocument/n1:title"/>
		</xsl:when>
		<xsl:otherwise>Clinical Document</xsl:otherwise>
	</xsl:choose>
</xsl:variable>

		
<xsl:template match="/n1:ClinicalDocument">
<html>
<head>
<!--	<style type="text/css" media="screen">@import "PPccr.css";</style>-->
		<title xml:space="preserve">
			<xsl:apply-templates select="n1:recordTarget/n1:patientRole/n1:patient/n1:name"/> - <xsl:value-of select="$title"/>
		</title>
<style type="text/css">

body {
	border-right-width: 0px; 
	border-top-width: 0px;
	border-left-width: 0px;
	border-bottom-width: 0px;
	padding-top: 0px;
	padding-bottom: 0px;
	padding-left: 0px;
	padding-right: 0px;
	margin-top: 0px;
	margin-bottom: 0px;
	margin-left: 0px;
	margin-right: 0px;
	border-collapse: collapse 
}

table.first {
	text-align: left;
	vertical-align: top;
	<!--background-color: #EFEBE7;-->
	background-color: #F5F9F9;
	border-color: #003366;
	border-right-width: 2px; 
	border-top-width: 2px;
	border-left-width: 2px;
	border-bottom-width: 2px;
	padding-top: 0px;
	padding-bottom: 0px;
	padding-left: 0px;
	padding-right: 0px;
	margin-top: 0px;
	margin-bottom: 0px;
	margin-left: 0px;
	margin-right: 0px;
	font: 95% "Times New Roman";
	border-collapse: collapse 
}

table.second {
	text-align: left;
	vertical-align: top;
	<!--background-color: #EFEBE7;-->
	background-color: #F5F9F9;
	border-color: #336699;
	border-right-width: 0px; 
	border-top-width: 0px;
	border-left-width: 0px;
	border-bottom-width: 0px;
	padding-top: 0px;
	padding-bottom: 0px;
	padding-left: 0px;
	padding-right: 0px;
	margin-top: 0px;
	margin-bottom: 0px;
	margin-left: 0px;
	margin-right: 0px;
	font: 95% "Times New Roman";
	border-collapse: collapse 
}

th.first {
	text-align: left;
	vertical-align: top;
	color: white;
	background-color: #002452;
	font: bold 175% "Times New Roman";
	padding-left: 3px;
	padding-right: 3px;
	border-collapse: collapse 
}

th.second {
	text-align: left;
	vertical-align: top;
	color: white;
	background-color: #00346B;
	font: bold	165% sans-serif;
	padding-left: 5px;
	padding-right: 3px;
	border-collapse: collapse 
}

th.toc {
	text-align: right;
	vertical-align: top;
	color: white;
	background-color: #00346B;
	font: bold sans-serif;
	padding-left: 5px;
	padding-right: 3px;
	border-collapse: collapse 
}

th.third {
	text-align: left;
	vertical-align: top;
	color: white;
	background-color: #085D8B;

	font: bold 140% sans-serif;
	padding-left: 7px;
	padding-right: 3px;
	border-collapse: collapse 
}

th.fourth {
	text-align: left;
	vertical-align: top;
	color: black;
	background-color: #71A1BC;
	<!-- background-color: #91A9BC;-->
	font: bold 115% sans-serif;
	padding-left: 9px;
	padding-right: 3px;
	border-collapse: collapse 
}

th.fifth {
	text-align: left;
	vertical-align: top;
	color: black;
	background-color: #6CB9E8;<!--108 185 232-->
	<!--background-color: #C0CDDF;-->
	font: bold 100% sans-serif;
	padding-left: 9px;
	padding-right: 3px;
	border-collapse: collapse 
}

th.content {
 text-align: left;
 vertical-align: top;
 padding-top: 2px;
 padding-bottom: 2px;
 padding-left: 9px;
 padding-right: 3px;
 border-collapse: collapse 
}


thead.fourth {
	text-align: left;
	vertical-align: top;
	color: black;
	<!--background-color: #91A9BC;-->
	background-color: #6CB9E8;<!--108 185 232-->
	font: bold 115% sans-serif;
	border-right-width: 2px; 
	border-top-width: 2px;
	border-left-width: 2px;
	border-bottom-width: 2px;
	padding-top: 0px;
	padding-bottom: 0px;
	padding-left: 0px;
	padding-right: 0px;
	margin-top: 0px;
	margin-bottom: 0px;
	margin-left: 0px;
	margin-right: 0px;
	border-collapse: collapse 
}

thead.fifth {
	text-align: left;
	vertical-align: top;
	color: black;
	background-color: #AFC8D7;<!--175 200 215-->
	<!--background-color: #C0CDDF;-->
	font: bold 100% sans-serif;
	padding-left: 9px;
	padding-right: 3px;
	border-collapse: collapse 
}

tr.first {
	text-align: left;
	vertical-align: top;
	color: black;
	<!--background-color: #E2E0E0;-->
	background-color: #E8F0F0;
	padding-top: 3px;
	padding-bottom: 3px;
	padding-left: 9px;
	padding-right: 3px;
	border-collapse: collapse 
}

tr.second {
	text-align: left;
	vertical-align: top;
	color: black;
	<!--background-color: #F9F4EF;-->
	background-color: #F0F5F5;
	padding-top: 3px;
	padding-bottom: 3px;
	padding-left: 9px;
	padding-right: 3px;
	border-collapse: collapse 
}


tr.content {
	text-align: left;
	vertical-align: top;
	padding-top: 2px;
	padding-bottom: 2px;
	padding-left: 9px;
	padding-right: 3px;
	border-collapse: collapse;
}

td.content  {
	padding-left: 9px;
	padding-right: 3px;
	padding-top: 2px;
	padding-bottom: 5px;
}

a.first 
{
	border-width: 0px;
	text-decoration: none;
	text-align: right;
	color: white;	
}


#smenu {
    z-index: 1;
    position: absolute;
    top: 45px;
    left: 685px;
	width: 100%;
	float: left;
	text-align: right;
	color: #000;
}
</style>

<style type="text/css">
#menu {
	position: absolute;
	top: 45px;
	left: 0px;
    z-index: 1;
	float: left;
	text-align: right;
	color: #000;
	list-style: none;
	line-height: 1;
}
</style>

<xsl:comment><![CDATA[[if lt IE 7]>
<style type="text/css">
#menu {
	display: none;
}
</style>
<![endif]]]></xsl:comment>

<style type="text/css">

#menu ul {
	list-style: none;
	margin: 0;
	padding: 0;
	width: 12em;
	float: right;
	text-align: right;
	color: #000;
}

#menu a, #menu h2 {
	font: bold 11px/16px arial, helvetica, sans-serif;
	text-align: right;
	display: block;
	border-width: 0px;
	border-style: solid;
	border-color: #ccc #888 #555 #bbb;
	margin: 0;
	padding: 2px 3px;
	color: #000;
}

#menu h2 {
	color: #fff;
	text-transform: uppercase;
	text-align: right;
}

#menu a {
	text-decoration: none;
	text-align: right;
	border-width: 1px;
	border-style: solid;
	border-color: #fff #777 #777 #777;
}

#menu a:hover {
	color: #000;
	background: #fff;
	text-align: right;
}

#menu li {
	position: relative;
}

#menu ul ul {
	position: relative;
	z-index: 500;
	text-align: left;
	color: #000;
	background-color: #E0E5E5;
	float: right;
}

#menu ul ul ul {
	position: absolute;
	top: 0;
	left: 100%;
	text-align: right;
	float: right;
}

div#menu ul ul,
div#menu ul li:hover ul ul,
div#menu ul ul li:hover ul ul
{display: none;}

div#menu ul li:hover ul,
div#menu ul ul li:hover ul,
div#menu ul ul ul li:hover ul
{display: block;}

</style>

</head>
<body xml:space="preserve">

<!--<center><img src="http://www.pmsi.com/images/banner.jpg"/></center><p/>-->
<center>
<!-- table of contents menu -->
<div id="menu"><center>
	<table border="0" cellspacing="0" cellpadding="0" width="100%">
		<tr>
			<td>&#160;</td>
			<td width="800">
				<ul>
					<li><h2>TOC [<font color="#fffacd">+</font>]</h2>
						<ul>
							<xsl:for-each select="n1:component">
								<xsl:for-each select="n1:structuredBody">
									<xsl:for-each select="n1:component">
										<xsl:for-each select="n1:section">
										<li><nobr><a><xsl:attribute name="href">#<xsl:value-of select="n1:title"/></xsl:attribute><xsl:value-of select="n1:title"/></a></nobr>
										<!-- submenus removed until positioning works
											<li><nobr><a><xsl:attribute name="href">#<xsl:value-of select="n1:title"/></xsl:attribute><xsl:value-of select="n1:title"/><xsl:if test="count(n1:component/n1:section)&gt;0">...</xsl:if></a></nobr>
											
												<xsl:if test="count(n1:component/n1:section)&gt;0">
													<ul>
														<xsl:for-each select="n1:component">
															<xsl:for-each select="n1:section">
																<li><nobr><a><xsl:attribute name="href">#<xsl:value-of select="n1:title"/></xsl:attribute><xsl:value-of select="n1:title"/></a></nobr></li>
															</xsl:for-each>
														</xsl:for-each>
													</ul>
												</xsl:if>
											-->
											</li>
										</xsl:for-each>
									</xsl:for-each>
								</xsl:for-each>
							</xsl:for-each>
						</ul>
					</li>
				</ul>
			</td>
			<td>&#160;</td>
		</tr>
	</table>
</center>
</div>
<table border="1" cellspacing="0" cellpadding="0" width="800" class="first">
	<th class="first">
		<center><nobr><a name="top"><xsl:apply-templates select="n1:recordTarget/n1:patientRole/n1:patient/n1:name"/> - <xsl:value-of select="$title"/></a></nobr></center>
	</th>
<!--Document Information -->
	<tr>
		<td>
			<table border="0" width="100%" cellspacing="0" cellpadding="0" class="second">
				<th class="second" colspan="2">
					<table>
						<tr>
							<td><nobr><xsl:text>Document Information</xsl:text></nobr></td>
				<td class="toc">
					<!--<div id="menu">
						<ul>
							<li><h2>TOC [+]</h2>
								<ul>
									<xsl:for-each select="n1:component">
										<xsl:for-each select="n1:structuredBody">
											<xsl:for-each select="n1:component">
												<xsl:for-each select="n1:section">
													<li><nobr><a href=""><xsl:value-of select="n1:title"/></a></nobr></li>
												</xsl:for-each>
											</xsl:for-each>
										</xsl:for-each>
									</xsl:for-each>
								</ul>
							</li>
						</ul>
					</div>-->
				</td>
				</tr></table></th>
				
				<tr>
					<td width="50%" height="100%" valign="top">
						<table border="0" width="100%" height="100%" class="second">
							<th colspan="2" class="fourth"><xsl:text>Authored By:</xsl:text></th>
							<xsl:for-each select="n1:author/n1:assignedAuthor">
								<xsl:if test="n1:assignedPerson/n1:name!=''">
									<tr class="content">
										<td class="content">
											<b><xsl:text>Name:</xsl:text></b>
										</td>
										<td class="content">
											<xsl:apply-templates select="n1:assignedPerson/n1:name"/>
										</td>
									</tr>
								</xsl:if>
								<xsl:if test="n1:addr!=''">
									<tr class="content">
										<td class="content">
											<b><xsl:text>Address:</xsl:text></b>
										</td>
										<td class="content">
											<xsl:apply-templates select="n1:addr"/>
										</td>
									</tr>
								</xsl:if>
								<xsl:for-each select="n1:telecom">
									<tr class="content">
										<td class="content">
											<nobr><b><xsl:apply-templates select="." mode="Label"/>:</b></nobr>
										</td>
										<td class="content">
											<nobr><xsl:apply-templates select="."/></nobr>
										</td>
									</tr>
								</xsl:for-each>
<!--
								<xsl:if test="CCR:Organization/CCR:Name!=''">
									<tr class="content">
										<td>
											<b><xsl:text>Organization:</xsl:text></b>
										</td>
										<td>
											<xsl:value-of select="CCR:Organization/CCR:Name"/>
										</td>
									</tr>
								</xsl:if>
-->
								<xsl:if test="n1:assignedAuthoringDevice!=''">
									<tr class="content">
										<td class="content">
											<nobr><b><xsl:text>System:</xsl:text></b></nobr>
										</td>
										<td class="content">
											<xsl:apply-templates select="n1:assignedAuthoringDevice/n1:manufacturerModelName" mode="DisplayGiven"/><xsl:apply-templates select="n1:assignedAuthoringDevice/n1:softwareName" mode="DisplayGiven"/>
										</td>
									</tr>
								</xsl:if>
							</xsl:for-each>
							<xsl:if test="/n1:ClinicalDocument/n1:legalAuthenticator/n1:assignedEntity/n1:assignedPerson/n1:name!=''">
								<tr class="content">
									<td class="content">
										<nobr><b><xsl:text>Signed by:</xsl:text></b></nobr>
									</td>
									<td class="content">
										<xsl:apply-templates select="/n1:ClinicalDocument/n1:legalAuthenticator/n1:assignedEntity/n1:assignedPerson/n1:name"/>
										<xsl:text> on </xsl:text>
										<xsl:apply-templates select="//n1:ClinicalDocument/n1:legalAuthenticator/n1:time" mode="Date"/>
									</td>
								</tr>
							</xsl:if>
							
							<tr height="100%"><td></td></tr>
						</table>
					</td>
					<td width="50%" height="100%" valign="top">
						<table border="0" width="100%" height="100%" class="second">
							<th colspan="2" class="fourth"><xsl:text>Detail:</xsl:text></th>
							<xsl:if test="n1:title!=''">
								<tr class="content">
									<td class="content">
										<b><xsl:text>Title:</xsl:text></b>
									</td>
									<td class="content">
										<xsl:apply-templates select="n1:title"/>
									</td>
								</tr>
							</xsl:if>
							<xsl:if test="count(n1:code)&gt;0">
								<tr class="content">
									<td class="content">
										<b>Description<xsl:if test="count(n1:code)&gt;1">s</xsl:if>:</b>
									</td>
									<td class="content">
										<xsl:for-each select="n1:code">
											<xsl:apply-templates select="." mode="CE"/><br/>
										</xsl:for-each>
									</td>
								</tr>
							</xsl:if>
							<xsl:if test="n1:effectiveTime/@value!=''">
								<tr class="content">
									<td class="content">
										<b><nobr><xsl:text>Effective Date:</xsl:text></nobr></b>
									</td>
									<td class="content">
										<xsl:apply-templates select="n1:effectiveTime" mode="Date"/>
									</td>
								</tr>
							</xsl:if>
							<xsl:for-each select="n1:telecom">
								<tr class="content">
									<td class="content">
										<nobr><b><xsl:apply-templates select="." mode="Label"/>:</b></nobr>
									</td>
									<td class="content">
										<nobr><xsl:apply-templates select="."/></nobr>
									</td>
								</tr>
							</xsl:for-each>
							<xsl:if test="n1:assignedAuthoringDevice!=''">
								<tr class="content">
									<td class="content">
										<nobr><b><xsl:text>System:</xsl:text></b></nobr>
									</td>
									<td class="content">
										<xsl:apply-templates select="n1:assignedAuthoringDevice/n1:manufacturerModelName" mode="DisplayGiven"/><xsl:apply-templates select="n1:assignedAuthoringDevice/n1:softwareName" mode="DisplayGiven"/>
									</td>
								</tr>
							</xsl:if>
							<tr height="100%"><td></td></tr>
						</table>
					</td>
				</tr>
			</table>
		</td>
	</tr>

<!--Patient Information -->
	<tr>
		<td>
			<table width="100%" cellspacing="0" cellpadding="0" class="second">
				<th colspan="3" class="second"><nobr><xsl:text>Patient Information</xsl:text></nobr></th>
				<tr>
					<td>
						<table width="100%" cellspacing="0" cellpadding="0" class="second">
							<th colspan="3" class="third"><nobr><xsl:text>Patient Detail</xsl:text></nobr></th>
							<xsl:for-each select="n1:recordTarget/n1:patientRole">
								<tr valign="top">
									<td width="50%">
										<table border="0" width="100%" cellspacing="0" cellpadding="0" class="second" valign="top">
										<tr class="content" valign="top">
											<td class="content">
												<b><xsl:text>Name:</xsl:text></b>
											</td>
											<td width="100%" class="content">
												<xsl:apply-templates select="n1:patient/n1:name"/>
											</td>
										</tr>
										<xsl:for-each select="n1:addr">
											<tr class="content">
												<td class="content">
													<b>Address:</b>
												</td>
												<td width="100%" class="content">
													<xsl:apply-templates select="."/>
												</td>
											</tr>
										</xsl:for-each>
										<xsl:for-each select="n1:telecom">
											<tr class="content">
												<td class="content">
													<nobr><b><xsl:apply-templates select="." mode="Label"/>:</b></nobr>
												</td>
												<td class="content">
													<nobr><xsl:apply-templates select="."/></nobr>
												</td>
											</tr>
										</xsl:for-each>
									</table>
								</td>
								<td width="50%">
									<table border="0" width="100%" cellspacing="0" cellpadding="0" class="second">
										<xsl:if test="n1:patient/n1:birthTime/@value!=''">
											<tr class="content">
												<td class="content">
													<b><nobr><xsl:text>Date of Birth:</xsl:text></nobr></b>
												</td>
												<td width="100%" class="content">
													<nobr><xsl:apply-templates select="n1:patient/n1:birthTime" mode="Date"/></nobr>
												</td>
											</tr>
										</xsl:if>
										<xsl:if test="n1:patient/n1:administrativeGenderCode/@code!=''">
											<tr class="content">
												<td class="content">
													<b><xsl:text>Gender:</xsl:text></b>
												</td>
												<td width="100%" class="content">
													<xsl:apply-templates select="n1:patient/n1:administrativeGenderCode" mode="Gender"/>
												</td>
											</tr>
										</xsl:if>
										<xsl:if test="n1:patient/n1:raceCode!=''">
											<tr class="content">
												<td class="content">
													<b><xsl:text>Race:</xsl:text></b>
												</td>
												<td width="100%" class="content">
													<xsl:apply-templates select="n1:patient/n1:raceCode" mode="CE"/>
												</td>
											</tr>
										</xsl:if>
										<xsl:if test="n1:patient/n1:ethnicGroupCode!=''">
											<tr class="content">
												<td class="content">
													<b><xsl:text>Ethnicity:</xsl:text></b>
												</td>
												<td width="100%" class="content">
													<xsl:apply-templates select="n1:patient/n1:ethnicGroupCode" mode="CE"/>
												</td>
											</tr>
										</xsl:if>
										<xsl:if test="n1:patient/n1:languageCommunication!=''">
											<tr class="content">
												<td class="content">
														<b><xsl:text>Language:</xsl:text></b>
												</td>
												<td width="100%" class="content">
													<xsl:apply-templates select="n1:patient/n1:languageCommunication" mode="LanguageCommunication"/>
												</td>
											</tr>
										</xsl:if>
									</table>
								</td>
								<td width="33%">
									<table border="0" width="100%" cellspacing="0" cellpadding="0" class="second">
										<xsl:for-each select="id">
											<tr class="content">
												<td>
													<b><nobr><xsl:value-of select="@root"/>:</nobr></b>
												</td>
												<td width="100%">
													<nobr><xsl:value-of select="@extension"/></nobr>
												</td>
											</tr>
										</xsl:for-each>
									</table>
								</td>
							</tr>		
						</xsl:for-each>

						</table>
					</td>
				</tr>
			</table>
		</td>
	</tr>
	
 <!-- 
********************************************************
  CDA Body
********************************************************
  --> 
  <xsl:apply-templates select="n1:component/n1:structuredBody"/>
</table>
</center>
</body>
</html>
</xsl:template>

<!-- StructuredBody -->
<xsl:template match="n1:component/n1:structuredBody">
    <xsl:apply-templates select="n1:component/n1:section"/>
</xsl:template>

<!-- Component/Section -->
<xsl:template match="n1:component/n1:section">
    <tr>
		<td>
		<xsl:variable name="tableClass">
			<xsl:choose>
				<xsl:when test="count(ancestor::n1:component/n1:section)=1">second</xsl:when>
				<xsl:when test="count(ancestor::n1:component/n1:section)=2">third</xsl:when>
				<xsl:when test="count(ancestor::n1:component/n1:section)=3">fourth</xsl:when>
			</xsl:choose>
		</xsl:variable>
		<xsl:variable name="thClass">
			<xsl:choose>
				<xsl:when test="count(ancestor::n1:component/n1:section)=1">third</xsl:when>
				<xsl:when test="count(ancestor::n1:component/n1:section)=2">fourth</xsl:when>
				<xsl:when test="count(ancestor::n1:component/n1:section)=3">fifth</xsl:when>
			</xsl:choose>
		</xsl:variable>
			<table width="100%" cellspacing="0" cellpadding="0">
				<xsl:attribute name="class"><xsl:value-of select="$tableClass"/></xsl:attribute>
				<th width="100%"><xsl:attribute name="class">
					<xsl:value-of select="$thClass"/></xsl:attribute>
					<nobr><a><xsl:attribute name="name"><xsl:value-of select="n1:title"/></xsl:attribute></a><xsl:value-of select="n1:title"/></nobr>
				</th>
				<th>
					<xsl:attribute name="class"><xsl:value-of select="$thClass"/></xsl:attribute>
					<small><a href="#top" title="Top of page" class="first"><font color="#fffacd">^</font></a></small>
				</th>
				<tr>
					<td colspan="2">
				<!--<xsl:choose>
					<xsl:when test="count(n1:entry)=0">-->
						<xsl:apply-templates select="n1:text"/>
					<!--</xsl:when>
					<xsl:otherwise>
						<xsl:apply-templates select="n1:entry"/>
					</xsl:otherwise>
				</xsl:choose>-->
					</td>
				</tr>
			</table>
		</td>
	</tr>

    <xsl:apply-templates select="n1:component/n1:section"/>
</xsl:template>


<xsl:template match="n1:name">
	<xsl:apply-templates select="n1:prefix" mode="DisplayGiven"/><xsl:apply-templates select="n1:given" mode="DisplayGiven"/><xsl:apply-templates select="n1:family" mode="DisplayGiven"/><xsl:apply-templates select="n1:suffix" mode="DisplayGiven"/>
</xsl:template>

<xsl:template match="n1:addr">
	<xsl:choose>
		<xsl:when test="count(*)=0">
			<xsl:value-of select="."/>
		</xsl:when>
		<xsl:otherwise>
			<xsl:if test="n1:careOf!=''">
				<nobr><xsl:value-of select="n1:careOf"/></nobr><br/>
			</xsl:if>
			<xsl:for-each select="n1:streetAddressLine">
				<nobr><xsl:value-of select="."/></nobr><br/>
			</xsl:for-each>
			<xsl:if test="n1:houseNumber!=''">
				<nobr><xsl:apply-templates select="n1:houseNumber" mode="DisplayGiven"/><xsl:apply-templates select="n1:direction" mode="DisplayGiven"/><xsl:apply-templates select="n1:streetName" mode="DisplayGiven"/></nobr><br/>
			</xsl:if>
			<xsl:if test="n1:city!=''"> 
				<nobr><xsl:value-of select="n1:city"/>, <xsl:apply-templates select="n1:state" mode="DisplayGiven"/><xsl:apply-templates select="n1:postalCode" mode="DisplayGiven"/></nobr><br/>
			</xsl:if>
			<xsl:if test="n1:country!=''"> 
				<nobr><xsl:value-of select="n1:country"/></nobr><br/>
			</xsl:if>
		</xsl:otherwise>
	</xsl:choose>
</xsl:template>

<xsl:template match="*" mode="Date">
	<xsl:choose>
		<!-- date only -->
		<xsl:when test="string-length(@value)=8">
			<xsl:apply-templates select="." mode="DateDisplay"/>
		</xsl:when>
		<!-- date + time (may include time zone which is not processed) -->
		<xsl:when test="string-length(@value)&gt;11">
			<xsl:apply-templates select="." mode="DateDisplay"/><xsl:text> </xsl:text><nobr>at <xsl:apply-templates select="." mode="TimeDisplay"/> <xsl:if test="substring(@value, 15, 1)='-' or substring(@value, 15, 1)='+'"><xsl:apply-templates select="." mode="TimeZoneDisplay"/></xsl:if></nobr>
		</xsl:when>
		<!-- unknown format -->
		<xsl:otherwise><xsl:value-of select="."/></xsl:otherwise>
	</xsl:choose>
</xsl:template>

<xsl:template match="*" mode="DateDisplay">
	<xsl:apply-templates select="." mode="DayOfWeekDisplay"/><xsl:text>, </xsl:text>
	<xsl:variable name="month" select="substring(@value, 5, 2)"/>
	<xsl:choose>
		<xsl:when test="$month='01'">
			<xsl:text>January </xsl:text>
		</xsl:when>
		<xsl:when test="$month='02'">
			<xsl:text>February </xsl:text>
		</xsl:when>
		<xsl:when test="$month='03'">
			<xsl:text>March </xsl:text>
		</xsl:when>
		<xsl:when test="$month='04'">
			<xsl:text>April </xsl:text>
		</xsl:when>
		<xsl:when test="$month='05'">
			<xsl:text>May </xsl:text>
		</xsl:when>
		<xsl:when test="$month='06'">
			<xsl:text>June </xsl:text>
		</xsl:when>
		<xsl:when test="$month='07'">
			<xsl:text>July </xsl:text>
		</xsl:when>
		<xsl:when test="$month='08'">
			<xsl:text>August </xsl:text>
		</xsl:when>
		<xsl:when test="$month='09'">
			<xsl:text>September </xsl:text>
		</xsl:when>
		<xsl:when test="$month='10'">
			<xsl:text>October </xsl:text>
		</xsl:when>
		<xsl:when test="$month='11'">
			<xsl:text>November </xsl:text>
		</xsl:when>
		<xsl:when test="$month='12'">
			<xsl:text>December </xsl:text>
		</xsl:when>
	</xsl:choose>
	<xsl:choose>
		<xsl:when test='substring(@value, 7, 1)="0"'>
			<xsl:value-of select="substring(@value, 8, 1)"/>
			<xsl:text>, </xsl:text>
		</xsl:when>
		<xsl:otherwise>
			<xsl:value-of select="substring(@value, 7, 2)"/>
			<xsl:text>, </xsl:text>
		</xsl:otherwise>
	</xsl:choose>
	<xsl:value-of select="substring(@value, 1, 4)"/>
</xsl:template>	

<xsl:template match="*" mode="DayOfWeekDisplay">
<!-- Using Zeller's Rule -->
<xsl:variable name="Month">
		<xsl:choose>
			<xsl:when test="substring(@value, 5, 2)&lt;3"><xsl:value-of select="substring(@value, 5, 2) + 10"/></xsl:when>
			<xsl:otherwise><xsl:value-of select="substring(@value, 5, 2) - 2"/></xsl:otherwise>
		</xsl:choose>
	</xsl:variable>
	<xsl:variable name="Century">
		<xsl:choose>
			<xsl:when test="$Month&gt;10"><xsl:value-of select="substring(substring(@value, 1, 4) - 1, 1, 2)"/></xsl:when>
			<xsl:otherwise><xsl:value-of select="substring(@value, 1, 2)"/></xsl:otherwise>
		</xsl:choose>
	</xsl:variable>
	<xsl:variable name="Year">
		<xsl:choose>
			<xsl:when test="$Month&gt;10"><xsl:value-of select="substring(substring(@value, 1, 4) - 1, 3, 2)"/></xsl:when>
			<xsl:otherwise><xsl:value-of select="substring(@value, 3, 2)"/></xsl:otherwise>
		</xsl:choose>
	</xsl:variable>	
	<xsl:variable name="Day" select="substring(@value, 7, 2)"/>
	
	<xsl:variable name="DayOfWeek">
		<xsl:choose>
			<xsl:when test="($Day + floor((13 * $Month - 1) div 5) + $Year + floor($Year div 4) + floor($Century div 4) - (2 * $Century)) mod 7&lt;0">
				<xsl:value-of select="($Day + floor((13 * $Month - 1) div 5) + $Year + floor($Year div 4) + floor($Century div 4) - (2 * $Century)) mod 7+7"/>
			</xsl:when>
			<xsl:otherwise>
				<xsl:value-of select="($Day + floor((13 * $Month - 1) div 5) + $Year + floor($Year div 4) + floor($Century div 4) - (2 * $Century)) mod 7"/>
			</xsl:otherwise>
		</xsl:choose>
	</xsl:variable>
	<xsl:choose>
		<xsl:when test="$DayOfWeek=0">Sunday</xsl:when>
		<xsl:when test="$DayOfWeek=1">Monday</xsl:when>
		<xsl:when test="$DayOfWeek=2">Tuesday</xsl:when>
		<xsl:when test="$DayOfWeek=3">Wednesday</xsl:when>
		<xsl:when test="$DayOfWeek=4">Thursday</xsl:when>
		<xsl:when test="$DayOfWeek=5">Friday</xsl:when>
		<xsl:when test="$DayOfWeek=6">Saturday</xsl:when>
	</xsl:choose>
</xsl:template>	

<xsl:template match="*" mode="TimeDisplay">
	<xsl:variable name="Hours"><xsl:choose><xsl:when test="substring(@value, 9, 2)=0">12</xsl:when><xsl:when test="substring(@value, 9, 2)&gt;12"><xsl:value-of select="substring(@value, 9, 2)-12"/></xsl:when><xsl:when test="substring(@value, 9, 1)=0"><xsl:value-of select="substring(@value, 10, 1)"/></xsl:when><xsl:otherwise><xsl:value-of select="substring(@value, 9, 2)"/></xsl:otherwise></xsl:choose></xsl:variable>
	<xsl:variable name="Meridian"><xsl:choose><xsl:when test="substring(@value, 9, 2)&gt;11"> pm</xsl:when><xsl:otherwise> am</xsl:otherwise></xsl:choose></xsl:variable>
	<xsl:value-of select="$Hours"/>:<xsl:value-of select="substring(@value, 11, 2)"/><xsl:if test="string-length(@value)&gt;5">:<xsl:value-of select="substring(@value, 13, 2)"/></xsl:if><xsl:value-of select="$Meridian"/>
</xsl:template>	

<xsl:template match="*" mode="TimeZoneDisplay">
	<xsl:text> </xsl:text><small><i>(<xsl:value-of select="substring(@value, 15, string-length(@value) - 14)"/>)</i></small>
</xsl:template>	

<xsl:template match="n1:telecom">
	<xsl:choose>
		<xsl:when test="substring(@value, 4, 1)=':'"><xsl:value-of select="substring(@value, 5, string-length(@value) - 4)"/>
		</xsl:when>
		<xsl:otherwise><xsl:value-of select="@value"/></xsl:otherwise>
	</xsl:choose>
</xsl:template>

<xsl:template match="n1:telecom" mode="Label">
	<xsl:variable name="Postfix"><xsl:choose><xsl:when test="substring(@value, 1, 3)='tel' and @use!='PG'">Telephone</xsl:when><xsl:when test="substring(@value, 1, 3)='fax'">Fax</xsl:when></xsl:choose></xsl:variable>
	<nobr><xsl:choose>
		<xsl:when test="@use='H'">Home</xsl:when>
		<xsl:when test="@use='HP'">Home</xsl:when>
		<xsl:when test="@use='HV'">Vacation Home</xsl:when>
		<xsl:when test="@use='WP'">Work</xsl:when>
		<xsl:when test="@use='DIR'">Direct</xsl:when>
		<xsl:when test="@use='BAD'">Bad</xsl:when>
		<xsl:when test="@use='TMP'">Temporary</xsl:when>
		<xsl:when test="@use='AS'">Answering Service</xsl:when>
		<xsl:when test="@use='EC'">Emergency</xsl:when>
		<xsl:when test="@use='MC'">Mobile</xsl:when>
		<xsl:when test="@use='PG'">Pager</xsl:when>
	</xsl:choose>
	<xsl:text> </xsl:text>
	<xsl:value-of select="$Postfix"/></nobr>
</xsl:template>


<xsl:template match="*" mode="CE">
	<xsl:choose>
		<xsl:when test="n1:originalText!=''">
			<xsl:value-of select="n1:originalText"/>
		</xsl:when>
		<xsl:when test="@code!='' or @displayName!=''">
			<xsl:choose>
				<xsl:when test="@displayName!=''">
					<nobr><xsl:value-of select="@displayName"/><xsl:text> </xsl:text></nobr>
					<xsl:if test="@code!=''">
						<small><i><nobr>(<xsl:apply-templates select="@code" mode="DisplayGiven"/><xsl:text> </xsl:text><xsl:value-of select="@codeSystemName"/>)</nobr></i></small>
					</xsl:if>
				</xsl:when>
			    <xsl:otherwise>
					<xsl:value-of select="@code"/> <xsl:value-of select="@codeSystemName"/>
			    </xsl:otherwise>
			</xsl:choose>
		</xsl:when>
		<xsl:otherwise><xsl:value-of select="."/></xsl:otherwise>
	</xsl:choose>
</xsl:template>

<xsl:template match="*" mode="Gender">
	<xsl:choose>
		<xsl:when test="@code='M'">Male</xsl:when>
		<xsl:when test="@code='F'">Female</xsl:when>
		<xsl:otherwise><xsl:value-of select="@code"/></xsl:otherwise>
	</xsl:choose>
</xsl:template>


<xsl:template match="*" mode="LanguageCommunication">
	<xsl:value-of select="languageCode"/>
</xsl:template>



<xsl:template match="*" mode="componentEntry">
	
</xsl:template>


<xsl:template match="*" mode="DisplayGiven"><xsl:if test="."><xsl:value-of select="."/><xsl:text> </xsl:text></xsl:if></xsl:template>


<!-- Component text rendering -->
<!--   Text   -->
<xsl:template match="n1:text" mode="componentText">
	<xsl:apply-templates/>
	<br/>
</xsl:template>

<!--      Tables   -->
<xsl:template match="n1:table/@*|n1:thead/@*|n1:tfoot/@*|n1:tbody/@*|n1:colgroup/@*|n1:col/@*|n1:tr/@*|n1:th/@*|n1:td/@*">
	<xsl:copy>
		<xsl:apply-templates/>
	</xsl:copy>
</xsl:template>

<xsl:template match="n1:table">
	<table class="second" width="100%">
		<xsl:apply-templates/>
	</table>
</xsl:template>

<xsl:template match="n1:thead">
	<thead class="fifth">
		<xsl:apply-templates/>
	</thead>
</xsl:template>

<xsl:template match="n1:tfoot">
	<tfoot>
		<xsl:apply-templates/>
	</tfoot>
</xsl:template>

<xsl:template match="n1:tbody">
	<tbody>
		<xsl:apply-templates/>
	</tbody>
</xsl:template>

<xsl:template match="n1:colgroup">
	<colgroup>
		<xsl:apply-templates/>
	</colgroup>
</xsl:template>

<xsl:template match="n1:col">
	<col>
		<xsl:apply-templates/>
	</col>
</xsl:template>

<xsl:template match="n1:tr">
	<xsl:variable name="Class">
		<xsl:if test="name(parent::node())!='thead'"><xsl:choose><xsl:when test="position() mod 2 = 0">first</xsl:when><xsl:otherwise>second</xsl:otherwise></xsl:choose></xsl:if>
	</xsl:variable>
	<tr class="{$Class}">
	
		<xsl:apply-templates/>
	</tr>
</xsl:template>

<xsl:template match="n1:th">
	<th class="content">
		<xsl:apply-templates/>
	</th>
</xsl:template>

<xsl:template match="n1:td">
	<td class="content">
		<xsl:apply-templates/>
	</td>
</xsl:template>

<xsl:template match="n1:table/n1:caption">
	<span style="font-weight:bold; ">
		<xsl:apply-templates/>
	</span>
</xsl:template>
    


<!--   paragraph  -->
<xsl:template match="n1:paragraph">
	<xsl:apply-templates/>
	<br/>
</xsl:template>

<!--     Content w/ deleted text is hidden -->
<xsl:template match="n1:content[@revised='delete']"/>

<!--   content  -->
<xsl:template match="n1:content">
	<xsl:apply-templates/>
</xsl:template>

<!--   list  -->
<xsl:template match="n1:list">
	<xsl:if test="n1:caption">
		<span style="font-weight:bold; ">
			<xsl:apply-templates select="n1:caption"/>
		</span>
	</xsl:if>
	<ul>
		<xsl:for-each select="n1:item">
			<li>
				<xsl:apply-templates/>
			</li>
		</xsl:for-each>
	</ul>
</xsl:template>

<xsl:template match="n1:list[@listType='ordered']">
	<xsl:if test="n1:caption">
		<span style="font-weight:bold; ">
			<xsl:apply-templates select="n1:caption"/>
		</span>
	</xsl:if>
	<ol>
		<xsl:for-each select="n1:item">
			<li>
				<xsl:apply-templates/>
			</li>
		</xsl:for-each>
	</ol>
</xsl:template>

<!--   caption  -->
<xsl:template match="n1:caption">
	<xsl:apply-templates/>
	<xsl:text>: </xsl:text>
</xsl:template>



<!--   RenderMultiMedia 

this currently only handles GIF's and JPEG's.  It could, however,
be extended by including other image MIME types in the predicate
and/or by generating <object> or <applet> tag with the correct
params depending on the media type  @ID  =$imageRef     referencedObject
-->
<xsl:template match="n1:renderMultiMedia">
	<xsl:variable name="imageRef" select="@referencedObject"/>
	<xsl:choose>
		<xsl:when test="//n1:regionOfInterest[@ID=$imageRef]">
			<!-- Here is where the Region of Interest image referencing goes -->
			<xsl:if test='//n1:regionOfInterest[@ID=$imageRef]//n1:observationMedia/n1:value[@mediaType="image/gif" or @mediaType="image/jpeg"]'>
				<br clear='all'/>
				<xsl:element name='img'>
					<xsl:attribute name='src'>graphics/
						<xsl:value-of select='//n1:regionOfInterest[@ID=$imageRef]//n1:observationMedia/n1:value/n1:reference/@value'/>
					</xsl:attribute>
				</xsl:element>
			</xsl:if>
		</xsl:when>
		<xsl:otherwise>
			<!-- Here is where the direct MultiMedia image referencing goes -->
			<xsl:if test='//n1:observationMedia[@ID=$imageRef]/n1:value[@mediaType="image/gif" or @mediaType="image/jpeg"]'>
				<br clear='all'/>
				<xsl:element name='img'>
					<xsl:attribute name='src'>graphics/
						<xsl:value-of select='//n1:observationMedia[@ID=$imageRef]/n1:value/n1:reference/@value'/>
					</xsl:attribute>
					</xsl:element>
			</xsl:if>
		</xsl:otherwise>
	</xsl:choose>
</xsl:template>

<!-- 	Stylecode processing   
Supports Bold, Underline and Italics display

-->

<xsl:template match="//n1:*[@styleCode]">
	<xsl:if test="@styleCode='Bold'">
		<xsl:element name='b'>
			<xsl:apply-templates/>
		</xsl:element>
	</xsl:if>

	<xsl:if test="@styleCode='Italics'">
		<xsl:element name='i'>
			<xsl:apply-templates/>
		</xsl:element>
	</xsl:if>

	<xsl:if test="@styleCode='Underline'">
		<xsl:element name='u'>
			<xsl:apply-templates/>
		</xsl:element>
	</xsl:if>

	<xsl:if test="contains(@styleCode,'Bold') and contains(@styleCode,'Italics') and not (contains(@styleCode, 'Underline'))">
		<xsl:element name='b'>
			<xsl:element name='i'>
				<xsl:apply-templates/>
			</xsl:element>
		</xsl:element>
	</xsl:if>

	<xsl:if test="contains(@styleCode,'Bold') and contains(@styleCode,'Underline') and not (contains(@styleCode, 'Italics'))">
		<xsl:element name='b'>
			<xsl:element name='u'>
				<xsl:apply-templates/>
			</xsl:element>
		</xsl:element>
	</xsl:if>

	<xsl:if test="contains(@styleCode,'Italics') and contains(@styleCode,'Underline') and not (contains(@styleCode, 'Bold'))">
		<xsl:element name='i'>
			<xsl:element name='u'>
				<xsl:apply-templates/>
			</xsl:element>
		</xsl:element>
	</xsl:if>

	<xsl:if test="contains(@styleCode,'Italics') and contains(@styleCode,'Underline') and contains(@styleCode, 'Bold')">
		<xsl:element name='b'>
			<xsl:element name='i'>
				<xsl:element name='u'>
					<xsl:apply-templates/>
				</xsl:element>
			</xsl:element>
		</xsl:element>
	</xsl:if>

</xsl:template>

<!-- 	Superscript or Subscript   -->
<xsl:template match="n1:sup">
	<xsl:element name='sup'>
		<xsl:apply-templates/>
	</xsl:element>
</xsl:template>

<xsl:template match="n1:sub">
	<xsl:element name='sub'>
		<xsl:apply-templates/>
	</xsl:element>
</xsl:template>

<!--  Bottomline  -->
<xsl:template name="bottomline">
	<br/>
	<br/>
	<b>
		<xsl:text>Signed by: </xsl:text>
	</b>
	<br/>
	<xsl:apply-templates select="/n1:ClinicalDocument/n1:legalAuthenticator/n1:assignedEntity/n1:assignedPerson/n1:name"/>
	<xsl:text> on </xsl:text>
	<xsl:apply-templates select="//n1:ClinicalDocument/n1:legalAuthenticator/n1:time" mode="Date"/>
</xsl:template>


<!-- END Component text rendering -->
</xsl:stylesheet>

 

