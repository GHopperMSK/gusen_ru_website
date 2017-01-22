<?xml version="1.0"?>
<xsl:stylesheet version="1.0"
xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
<xsl:output method="xml" indent="yes" omit-xml-declaration="yes" />

<xsl:output indent="no" method="html" />

<xsl:template match="unit[position() mod 2 = 1]">
	<xsl:apply-templates mode="proc" select=".|following-sibling::unit[not(position() > 1)]" />
	<div class="clearfix hidden-lg hidden-md"></div>
</xsl:template>

<xsl:template match="unit" mode="proc"> 
    <div class="col-lg-3 col-md-3 col-sm-6 col-xs-6" itemscope="" itemtype="http://schema.org/Product">
    	<link itemprop="itemCondition" href="http://schema.org/UsedCondition" />
        <meta itemprop="category">
            <xsl:attribute name="content"><xsl:value-of select="category"/></xsl:attribute>
        </meta>
        <meta itemprop="brand manufacturer">
            <xsl:attribute name="content"><xsl:value-of select="manufacturer"/></xsl:attribute>
        </meta>
        <meta itemprop="model">
            <xsl:attribute name="content"><xsl:value-of select="@name"/></xsl:attribute>
        </meta>
        <meta itemprop="description">
            <xsl:attribute name="content"><xsl:value-of select="manufacturer"/>&#160;<xsl:value-of select="@name"/>, <xsl:value-of select="year"/>&#160;г.</xsl:attribute>
        </meta>
    	
    	<a itemprop="url">
        	<xsl:attribute name="href">/unit/<xsl:value-of select="@id"/></xsl:attribute>
        	<div class="itemz">
            <img class="img-responsive" itemprop="image">
                <xsl:attribute name="alt"><xsl:value-of select="manufacturer"/><xsl:text> </xsl:text><xsl:value-of select="@name"/>
                </xsl:attribute>
                <xsl:attribute name="src">/images/tmb/<xsl:value-of select="images/img"/>
                </xsl:attribute>
            </img>
            
            <div class="prices" itemprop="offers" itemscope="" itemtype="http://schema.org/Offer">
            	<xsl:value-of select='translate(format-number(price, "###,###"),","," ")'/>&#160;₽
                <meta itemprop="price">
                    <xsl:attribute name="content"><xsl:value-of select="price"/></xsl:attribute>
                </meta>
                <meta itemprop="priceCurrency" content="RUB" />
                <link itemprop="availability" href="http://schema.org/InStock" />
            </div>
            <div class="infomachines">
            	<p><xsl:value-of select="year"/>&#160;г.</p>
                <p><xsl:value-of select="city"/></p>
            </div>
            </div>
            <h3 class="title-name" itemprop="name"><xsl:value-of select="manufacturer"/>&#160;<xsl:value-of select="@name"/></h3>
        </a>
    </div>
</xsl:template>

<xsl:template match="root">
    <xsl:for-each select="category">
        <xsl:if test="node()">
            <div class="row row-content">
                <div>
	            	<a>
	                	<xsl:attribute name="href">/search/<xsl:value-of select="@id" />
	                	</xsl:attribute>
	                	<h2 class="title-category"><xsl:value-of select="@name" /></h2>
	                </a>
                </div>
            </div>

		    <div class="row row-itemz">
				<xsl:apply-templates />
            </div>
        
            <xsl:if test="count(*) = 4">
	            <div>
	                <a class="allc">
	                	<xsl:attribute name="href">/search/<xsl:value-of select="@id"/>
	                    </xsl:attribute>Все предложения из категории
	                </a>
	            </div>
            </xsl:if>            

        </xsl:if>
    </xsl:for-each>
</xsl:template>

<xsl:template match="unit[not(position() mod 2 = 1)]"/> 

</xsl:stylesheet>