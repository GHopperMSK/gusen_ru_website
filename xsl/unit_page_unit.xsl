<?xml version="1.0"?>
<xsl:stylesheet version="1.0"
xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
<xsl:output method="xml" indent="yes" omit-xml-declaration="yes" />

<xsl:output indent="no" method="html" />

<xsl:template name="break">
  <xsl:param name="text" select="string(.)"/>
  <xsl:choose>
    <xsl:when test="contains($text, '&#xa;')">
        <xsl:value-of select="substring-before($text, '&#xa;')" />
        <br />
        <xsl:call-template name="break">
        <xsl:with-param 
            name="text" 
            select="substring-after($text, '&#xa;')"
        />
        </xsl:call-template>
    </xsl:when>
    <xsl:otherwise>
        <xsl:value-of select="$text"/>
    </xsl:otherwise>
  </xsl:choose>
</xsl:template>

<xsl:template match="unit">
    <div class="row row-content" itemscope="" itemtype="http://schema.org/Product">
        <link itemprop="itemCondition" href="http://schema.org/UsedCondition"/>
        <meta itemprop="category">
            <xsl:attribute name="content"><xsl:value-of select="category"/></xsl:attribute>
        </meta>
        <meta itemprop="brand manufacturer">
            <xsl:attribute name="content"><xsl:value-of select="manufacturer"/></xsl:attribute>
        </meta>
        <meta itemprop="model">
            <xsl:attribute name="content"><xsl:value-of select="@name"/></xsl:attribute>
        </meta>
        <div class="slider-header">
            <a>
                <xsl:attribute name="href">/search/<xsl:value-of select="category/@id" />
                </xsl:attribute>
                <h2>
                    <xsl:value-of select="category"/>
                </h2>
            </a>
            /
            <h3><xsl:value-of select="manufacturer"/>&#160;<xsl:value-of select="@name"/></h3>
        </div>
        <div class="wrap-fotorama">
            <div class="price"><xsl:value-of select='translate(format-number(price, "###,###"),","," ")'/>&#160;₽</div>
            <div class="fotorama" data-allowfullscreen="true" data-width="100%" data-maxheight="550" data-loop="true" data-arrows="true" data-click="true" data-swipe="true" data-fit="cover">

                <xsl:for-each select="images/img">
                    <img>
                        <xsl:if test="position() = 1">
                            <xsl:attribute name="itemprop">image</xsl:attribute>
                        </xsl:if>
                        <xsl:attribute name="src">/images/<xsl:value-of select="text()"/></xsl:attribute>
                        <xsl:attribute name="alt"><xsl:value-of select="/root/unit/manufacturer"/><xsl:text> </xsl:text><xsl:value-of select="/root/unit/@name"/></xsl:attribute>                    
                    </img>
                </xsl:for-each>
            </div>
        </div>

        <div class="row row-info">
            <div class="col-lg-6 col-md-6 col-sm-12 col-xs-12">
                <h1 itemprop="name"><xsl:value-of select="manufacturer"/>&#160;<xsl:value-of select="@name"/></h1>
                <p>Федеральный округ: <span><xsl:value-of select="fdistrict" /></span></p>
                <p>Регион: <span><xsl:value-of select="region" /></span></p>
                <p>Город: <span><xsl:value-of select="city" /></span></p>
                <p>Год выпуска: <span><xsl:value-of select="year"/></span></p>
                <xsl:if test="mileage">
                    <p>Пробег: <span><xsl:value-of select='translate(format-number(mileage, "###,###"),","," ")'/>&#160;км.</span></p>
                </xsl:if>
                <xsl:if test="op_time">
                    <p>Наработка: <span><xsl:value-of select='translate(format-number(op_time, "###,###"),","," ")'/>&#160;м/ч.</span></p>
                </xsl:if>
                <p itemprop="offers" itemscope="" itemtype="http://schema.org/Offer">
                    Цена: <span>
                        <xsl:if test="@is_arch='TRUE'">
                            <xsl:attribute name="class">unit_sold_out</xsl:attribute>
                        </xsl:if>
                        <xsl:value-of select='translate(format-number(price, "###,###"),","," ")'/> ₽
                    </span>
                    <xsl:if test="@is_arch='TRUE'">&#160;(продано)</xsl:if>
                    <meta itemprop="price">
                        <xsl:attribute name="content"><xsl:value-of select="price"/></xsl:attribute>
                    </meta>
                    <meta itemprop="priceCurrency" content="RUB" />
                    <link itemprop="availability" href="http://schema.org/InStock" />
                </p>
            </div>                   
            <div class="col-lg-6 col-md-6 col-sm-12 col-xs-12">
                <p class="main_inf">Описание:</p>
                <p itemprop="description">
                    <xsl:call-template name="break">
                        <xsl:with-param name="text" select="description" />
                    </xsl:call-template>
                </p>
            </div> 
        </div>
    </div>

                  
</xsl:template>

</xsl:stylesheet>