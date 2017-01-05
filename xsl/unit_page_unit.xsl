<?xml version="1.0"?>
<xsl:stylesheet version="1.0"
xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
<xsl:output method="xml" indent="yes" omit-xml-declaration="yes" />

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
    <div id="thumbImg">
        <xsl:for-each select="images/img">
            <a data-lightbox="lb_image">
                <xsl:attribute name="href">/images/<xsl:value-of select="text()"/>
                </xsl:attribute>
                <img>
                    <xsl:attribute name="src">/images/tmb/<xsl:value-of select="text()"/>
                    </xsl:attribute>
                    <xsl:attribute name="alt"><xsl:value-of select="/root/unit/manufacturer"/><xsl:text> </xsl:text><xsl:value-of select="/root/unit/@name"/>
                    </xsl:attribute>                    
                </img>
            </a>
        </xsl:for-each>
    </div>
    
    <h1>
        <a>
            <xsl:attribute name="href">/search/<xsl:value-of select="category/@id" /></xsl:attribute><xsl:value-of select="category"/>
        </a> / <xsl:value-of select="manufacturer"/>&#160;<xsl:value-of select="@name"/>
    </h1>

    <p>Федеральный округ: <xsl:value-of select="fdistrict"/></p>
    <p>Регион: <xsl:value-of select="region"/></p>
    <p>Город: <xsl:value-of select="city"/></p>
    <p>Год выпуска: <xsl:value-of select="year"/></p>
    <xsl:if test="mileage">
    <p>Пробег: <xsl:value-of select='translate(format-number(mileage, "###,###"),","," ")'/>&#160;км.</p>
    </xsl:if>
    <xsl:if test="op_time">
    <p>Наработка: <xsl:value-of select='translate(format-number(op_time, "###,###"),","," ")'/>&#160;час.</p>
    </xsl:if>
    <p>Цена: <xsl:value-of select='translate(format-number(price, "###,###"),","," ")'/>&#160;₽</p>
    
    <p>
        <xsl:call-template name="break">
            <xsl:with-param name="text" select="description" />
        </xsl:call-template>
    </p>                        
</xsl:template>

</xsl:stylesheet>