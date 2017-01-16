<?xml version="1.0"?>
<xsl:stylesheet version="1.0"
xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
<xsl:output method="xml" indent="yes" omit-xml-declaration="yes" />

<xsl:output indent="no" method="html" />

<xsl:template name="break">
  <xsl:param name="text" select="string(.)"/>
  <xsl:choose>
    <xsl:when test="contains($text, '&#xa;')">
      <xsl:value-of select="substring-before($text, '&#xa;')"/>
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

    <div class="row row-content">
        <h3 class="slider-header">
            <a><xsl:attribute name="href">/search/<xsl:value-of select="category/@id" /></xsl:attribute><xsl:value-of select="category"/></a> / <xsl:value-of select="manufacturer"/>&#160;<xsl:value-of select="@name"/>
        </h3>
        <div class="wrap-fotorama">
            <div class="price"><xsl:value-of select='translate(format-number(price, "###,###"),","," ")'/>&#160;₽</div>
            <div class="fotorama" data-allowfullscreen="true" data-loop="true" data-arrows="true" data-click="true" data-swipe="true" data-fit="cover">

                <xsl:for-each select="images/img">
                    <img>
                        <xsl:attribute name="src">/images/<xsl:value-of select="text()"/></xsl:attribute>
                        <xsl:attribute name="alt"><xsl:value-of select="/root/unit/manufacturer"/><xsl:text> </xsl:text><xsl:value-of select="/root/unit/@name"/></xsl:attribute>                    
                    </img>
                </xsl:for-each>
            </div>
        </div>

        <div class="row row-info">
            <div class="col-lg-6 col-md-6 col-sm-12 col-xs-12">
                <h2><xsl:value-of select="manufacturer"/>&#160;<xsl:value-of select="@name"/></h2>
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
            </div>                   
            <div class="col-lg-6 col-md-6 col-sm-12 col-xs-12">
                <p class="main_inf">Описание:</p>
                <p>
                    <xsl:call-template name="break">
                        <xsl:with-param name="text" select="description" />
                    </xsl:call-template>
                </p>
            </div> 
        </div>


    </div>

                  
</xsl:template>

</xsl:stylesheet>