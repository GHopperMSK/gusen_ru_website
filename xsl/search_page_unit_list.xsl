<?xml version="1.0"?>
<xsl:stylesheet version="1.0"
xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
<xsl:output method="xml" indent="yes" omit-xml-declaration="yes" />

<xsl:output indent="no" method="html" />

<xsl:template match="unit[position() mod 2 = 1]">
	<xsl:choose>
		<xsl:when test="position() = 1">
		</xsl:when>
		<xsl:when test="position() mod 4 = 1">
			<div class="clearfix"></div>
		</xsl:when>
		<xsl:otherwise>
			<div class="clearfix hidden-lg hidden-md"></div>
		</xsl:otherwise>
	</xsl:choose>
	<xsl:apply-templates mode="proc" select=".|following-sibling::unit[not(position() > 1)]" />
</xsl:template>

<xsl:template match="unit" mode="proc"> 
    <div class="col-lg-3 col-md-3 col-sm-6 col-xs-6">
    	<a>
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
	</div> 
</xsl:template>

<xsl:template match="unit[not(position() mod 2 = 1)]"/> 

<xsl:template match="root">
    <div class="row row-itemz">
    	<xsl:apply-templates />
    </div>
</xsl:template>

</xsl:stylesheet>