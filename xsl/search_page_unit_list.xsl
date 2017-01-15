<?xml version="1.0"?>
<xsl:stylesheet version="1.0"
xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
<xsl:output method="xml" indent="yes" omit-xml-declaration="yes" />

<xsl:template match="root">

    <div class="row row-itemz">
    <xsl:for-each select="unit">
    
        <a class="collg-3 col-md-3 col-sm-4 col-xs-6">
        	<xsl:attribute name="href">/unit/<xsl:value-of select="@id"/></xsl:attribute>
        	<div class="itemz">
            <img class="img-responsive">
                <xsl:attribute name="alt"><xsl:value-of select="manufacturer"/><xsl:text> </xsl:text><xsl:value-of select="@name"/>
                </xsl:attribute>
                <xsl:attribute name="src">/images/tmb/<xsl:value-of select="images/img"/>
                </xsl:attribute>
            </img>
            
            <div class="prices">
            	<xsl:value-of select='translate(format-number(price, "###,###"),","," ")'/>&#160;₽
            </div>
            <div class="infomachines">
            	<p><xsl:value-of select="year"/>&#160;г.</p>
                <p><xsl:value-of select="city"/></p>
            </div>
            </div>
            <p><xsl:value-of select="manufacturer"/>&#160;<xsl:value-of select="@name"/></p>
        </a>    
    
    </xsl:for-each>
    </div>
            
            
</xsl:template>

</xsl:stylesheet>