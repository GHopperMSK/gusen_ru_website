<?xml version="1.0"?>
<xsl:stylesheet version="1.0"
xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
<xsl:output method="xml" infdent="yes" omit-xml-declaration="yes" />



<xsl:template match="root">

    <xsl:choose>
        <xsl:when test="user">
            <p class="user">
            <img>
                <xsl:attribute name="src">
                    <xsl:value-of select="/root/user/img" />
                </xsl:attribute>
            </img>&#160;
            <xsl:value-of select="/root/user/@name"/>&#160;
            <a href="?page=logout">(выйти)</a></p>
        </xsl:when>
        <xsl:otherwise>
            <p>Авторизируйтесь через: 
            <xsl:for-each select="snetwork">
            <a class="social_link">
            <xsl:attribute name="href">
            <xsl:value-of select="link" />
            </xsl:attribute><xsl:value-of select="@name"/></a>&#160;
            </xsl:for-each>
            </p>
        </xsl:otherwise>
    </xsl:choose>
    
</xsl:template>

</xsl:stylesheet>