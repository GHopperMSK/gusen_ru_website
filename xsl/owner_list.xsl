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

<xsl:template match="owner">
	<div class="row">
	    <div class="col-md-2">
	    	<xsl:value-of select="@name"/><br />
            <p>
                <a class="btn btn-info">
                <xsl:attribute name="href">?page=admin&amp;act=owner_form&amp;id=<xsl:value-of select="@id"/>
                </xsl:attribute>Edit</a>&#160;
                <a class="btn btn-danger" onclick="return confirm('Are you sure you want to delete the owner?');">
                <xsl:attribute name="href">?page=admin&amp;act=owner_delete&amp;id=<xsl:value-of select="@id"/>
                </xsl:attribute>Delete</a>
            </p>
	    </div>
	    <div class="col-md-10">
			 <xsl:call-template name="break">
                <xsl:with-param name="text" select="current()" />
            </xsl:call-template>
	    </div>
	</div><br />
</xsl:template>


<xsl:template match="root">
	<xsl:apply-templates select="owner" />
</xsl:template>


</xsl:stylesheet>