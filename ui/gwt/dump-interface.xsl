<?xml version="1.0"?>
<!--

XSL transform to pull strings out of the interface definitions

-->
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:fn="http://www.w3.org/2005/xpath-functions" version="1.0">
	<xsl:output method="text" />
	<xsl:template match="/">
		<xsl:for-each select="//SimpleUIBuilder/Elements/Element/@title">
			<xsl:value-of select="concat(., '=', '&#xA;')"/>
		</xsl:for-each>
		<xsl:for-each select="//SimpleUIBuilder/Elements/Element/@help">
			<xsl:value-of select="concat(., '=', '&#xA;')"/>
		</xsl:for-each>
	</xsl:template>
</xsl:stylesheet>
