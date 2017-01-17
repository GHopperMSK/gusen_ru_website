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

<xsl:template match="root">

    <xsl:for-each select="unit">
        <article class="unit">
            <img>
                <xsl:attribute name="src">images/tmb/<xsl:value-of select="images/img"/>
                </xsl:attribute>
            </img>
            <p>
                <a>
                <xsl:attribute name="href">?page=admin&amp;act=admin_unit_form&amp;id=<xsl:value-of select="@id"/>
                </xsl:attribute>Edit</a>&#160;
                <a onclick="return confirm('Are you sure you want to delete the unit?');">
                <xsl:attribute name="href">?page=admin&amp;act=unit_del&amp;id=<xsl:value-of select="@id"/>
                </xsl:attribute>Delete</a>&#160;
                <a onclick="return confirm('Are you sure you want to move the unit to the archive?!');">
                <xsl:attribute name="href">?page=admin&amp;act=unit_arch&amp;id=<xsl:value-of select="@id"/>
                </xsl:attribute>Move to archive</a>&#160;
                <!--<a><xsl:attribute name="href"><xsl:value-of select="vk_link"/></xsl:attribute>Post in VK</a>-->
            </p>

            <a>
                <xsl:attribute name="href">?page=admin&amp;act=main&amp;vType=<xsl:value-of select="category/@cat_id"/>
                </xsl:attribute>
                <xsl:value-of select="category"/>
            </a>&#160;/&#160;

            <a>
                <xsl:attribute name="href">?page=unit&amp;id=<xsl:value-of select="@id"/>
                </xsl:attribute>
                <xsl:value-of select="manufacturer"/>&#160;<xsl:value-of select="@name"/>
            </a>
            <p><xsl:value-of select="fdistrict"/> / <xsl:value-of select="region"/> / <xsl:value-of select="city"/></p>
            <p>Год выпуска: <xsl:value-of select="year"/></p>
            <xsl:if test="mileage">
            <p>Пробег: <xsl:value-of select='translate(format-number(mileage, "###,###"),","," ")'/>&#160;км.</p>
            </xsl:if>
            <xsl:if test="op_time">
            <p>Наработка: <xsl:value-of select='translate(format-number(op_time, "###,###"),","," ")'/>&#160;час.</p>
            </xsl:if>
            <p>Цена: <xsl:value-of select='translate(format-number(price, "###,###"),","," ")'/>&#160;₽</p>
            <p class="description">
                <xsl:call-template name="break">
                    <xsl:with-param name="text" select="description" />
                </xsl:call-template>
            </p>                        
        </article>
    </xsl:for-each>
            
            
</xsl:template>

</xsl:stylesheet>