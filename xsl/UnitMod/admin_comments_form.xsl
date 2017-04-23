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

<xsl:template match="comment">
    <div class="col-lg-12">
        <input type="hidden" name="comment_id[]">
            <xsl:attribute name="value">
                <xsl:value-of select="@id"/>
            </xsl:attribute>
        </input>
        <input type="checkbox" name="approved[]">
            <xsl:attribute name="checked">
            </xsl:attribute>        
            <xsl:attribute name="value">
                <xsl:value-of select="@id"/>
            </xsl:attribute>
        </input>
        <!--<a target="_blank">-->
        <!--    <xsl:attribute name="href"><xsl:value-of select="link" />-->
        <!--    </xsl:attribute>-->
        <xsl:call-template name="break">
            <xsl:with-param name="text" select="text" />
        </xsl:call-template><br />
        <!--</a>-->
    </div>
</xsl:template>


<xsl:template match="root">
	<form method="POST" action="/?page=admin&amp;act=comments_approve">
	    <xsl:apply-templates select="comments/comment" />
	    <input class="search_button" type="submit" />
	</form>
</xsl:template>


</xsl:stylesheet>