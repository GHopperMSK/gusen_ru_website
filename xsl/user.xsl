<?xml version="1.0"?>
<xsl:stylesheet version="1.0"
xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
<xsl:output method="xml" infdent="yes" omit-xml-declaration="yes" />

<xsl:output indent="no" method="html" />

<xsl:template match="root">

    <xsl:choose>
        <xsl:when test="user">
            <div class="row row-comments">
            	<div class="comment_ph col-md-1">
	            <img><xsl:attribute name="src"><xsl:value-of select="/root/user/img" /></xsl:attribute></img>
	            </div>
	            <xsl:value-of select="/root/user/@name"/>&#160;
	            <a href="/logout">(выйти)</a>
            </div>
        </xsl:when>
        <xsl:otherwise>
        	<div class="row row-auth">Для добавления комментария авторизуйтесь через соц. сети: 
	            <xsl:for-each select="snetwork">
	            	<xsl:choose>
	            		<xsl:when test="@type='vk'">
			            	<a class="footp4 socseti vk"><xsl:attribute name="href"><xsl:value-of select="link" /></xsl:attribute><i class="fa fa-vk" aria-hidden="true">&#160;</i></a>
			            </xsl:when>
	            		<xsl:when test="@type='fb'">
			            	<a class="footp5 socseti facebook"><xsl:attribute name="href"><xsl:value-of select="link" /></xsl:attribute><i class="fa fa-facebook" aria-hidden="true">&#160;</i></a>
			            </xsl:when>
	            		<xsl:when test="@type='gl'">
			            	<a class="footp4 socseti vk"><xsl:attribute name="href"><xsl:value-of select="link" /></xsl:attribute><i class="fa fa-google" aria-hidden="true">&#160;</i></a>
			            </xsl:when>
			        </xsl:choose>
            </xsl:for-each>
            </div>
        </xsl:otherwise>
    </xsl:choose>
    
</xsl:template>

</xsl:stylesheet>