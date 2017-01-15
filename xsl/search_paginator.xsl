<?xml version="1.0"?>
<xsl:stylesheet version="1.0"
xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
<xsl:output method="xml" indent="yes" omit-xml-declaration="yes" />

<xsl:template match="root">

    <xsl:for-each select="page">
    
    <xsl:choose>
        <xsl:when test = "@type = 'current'">[<xsl:value-of select="number"/>]</xsl:when>
        <xsl:when test = "@type = 'first'"><a><xsl:attribute name="href"><xsl:value-of select="link"/></xsl:attribute>[&lt;]</a>&#160;...</xsl:when>
        <xsl:when test = "@type = 'last'">...&#160;<a><xsl:attribute name="href"><xsl:value-of select="link"/></xsl:attribute>[&gt;]</a></xsl:when>
        <xsl:otherwise><a><xsl:attribute name="href"><xsl:value-of select="link"/></xsl:attribute>[<xsl:value-of select="number"/>]</a></xsl:otherwise>
    </xsl:choose>
    
    </xsl:for-each>        
            
</xsl:template>

</xsl:stylesheet>