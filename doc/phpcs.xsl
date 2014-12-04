<?xml version="1.0" encoding="UTF-8"?>

<!--
    Document   : phpcs.xsl
    Created on : December 27, 2010, 1:42 PM
    Author     : schkovich
    Description:
        Transformation PHP_CodeSniffer xml report into human readable format.
-->

<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">
    <xsl:output method="html"  encoding="UTF-8"/>

    <!-- TODO customize transformation rules
         syntax recommendation http://www.w3.org/TR/xslt
    -->
    <xsl:template match="/">
        <html>
            <head>
                <title>phpcs.xsl</title>
                <link href="./doc/phpcs.css" rel="stylesheet" type="text/css" />
            </head>
            <body>
                <table>
                    <thead>
                        <tr>
                            <th class="file">Name</th>
                            <th class="notes">Errors</th>
                            <th class="notes">Warnings</th>
                        </tr>
                    </thead>
                    <tbody>
                        <xsl:for-each select="phpcs/file">
                            <tr>
                                <td>
                                    <xsl:value-of select="@name" />
                                </td>
                                <td>
                                    <xsl:value-of select="@errors" />
                                </td>
                                <td>
                                    <xsl:value-of select="@warnings" />
                                </td>
                            </tr>
                            <tr>
                                <td colspan="3">
                                    <xsl:for-each select="error">
                                        <span class="error">Error: </span>
                                        <xsl:value-of select="self::node()"/>
                                        <br />
                                        <b>Line:</b>
                                        <xsl:value-of select="@line" />
                                        <br />
                                        <b>Column:</b>
                                        <xsl:value-of select="@column" />
                                        <br />
                                        <b>Source:</b>
                                        <xsl:value-of select="@source" />
                                        <hr />
                                    </xsl:for-each>
                                    <xsl:for-each select="warning">
                                        <span class="warning">Warning: </span>
                                        <xsl:value-of select="self::node()"/>
                                        <br />
                                        <b>Line:</b>
                                        <xsl:value-of select="@line" />
                                        <br />
                                        <b>Column:</b>
                                        <xsl:value-of select="@column" />
                                        <br />
                                        <b>Source:</b>
                                        <xsl:value-of select="@source" />
                                        <hr />
                                    </xsl:for-each>
                                </td>
                            </tr>
                        </xsl:for-each>
                    </tbody>
                </table>
            </body>
        </html>
    </xsl:template>

</xsl:stylesheet>
