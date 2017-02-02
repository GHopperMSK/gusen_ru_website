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
        <div class="col-lg-12 admin_page_unit_list_div clearfix">
            <img class="pull-left">
                <xsl:attribute name="src">images/tmb/<xsl:value-of select="images/img"/>
                </xsl:attribute>
            </img>
            <p>
                <a class="btn btn-info">
                <xsl:attribute name="href">?page=admin&amp;act=admin_unit_form&amp;id=<xsl:value-of select="@id"/>
                </xsl:attribute>Edit</a>&#160;
                <a class="btn btn-info" href="#">
                	<xsl:attribute name="onclick">
                		wallPost(<xsl:value-of select="@id"/>); return false;
                	</xsl:attribute>
                	Post in VK
                </a>&#160;
                <a class="btn btn-warning" onclick="return confirm('Are you sure you want to move the unit to the archive?!');">
                <xsl:attribute name="href">?page=admin&amp;act=unit_arch&amp;id=<xsl:value-of select="@id"/>
                </xsl:attribute>Move to archive</a>&#160;
                <a class="btn btn-danger" onclick="return confirm('Are you sure you want to delete the unit?');">
                <xsl:attribute name="href">?page=admin&amp;act=unit_del&amp;id=<xsl:value-of select="@id"/>
                </xsl:attribute>Delete</a>
            </p>
			<p>
	            <a>
	                <xsl:attribute name="href">?page=admin&amp;act=main&amp;vType=<xsl:value-of select="category/@cat_id"/>
	                </xsl:attribute>
	                <xsl:value-of select="category"/>
	            </a>
	            
	            /
	
	            <a>
	                <xsl:attribute name="href">/unit/<xsl:value-of select="@id"/>
	                </xsl:attribute>
	                <xsl:value-of select="manufacturer"/>&#160;<xsl:value-of select="@name"/>
	            </a>
			</p>
			<p><xsl:value-of select="owner" /></p>
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
        </div>
        <div class="clearfix"></div>
    </xsl:for-each>
            
            
</xsl:template>

</xsl:stylesheet>