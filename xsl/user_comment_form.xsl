<?xml version="1.0"?>
<xsl:stylesheet version="1.0"
xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
<xsl:output method="xml" infdent="yes" omit-xml-declaration="yes" />

<xsl:output indent="no" method="html" />

<xsl:template match="root">

    <xsl:choose>
        <xsl:when test="user">
        	<li class="media">
	        	<div class="pull-left"><img><xsl:attribute name="src"><xsl:value-of select="/root/user/img" /></xsl:attribute></img></div>
	        	<div class="media-body">
	        		<h4 class="media-heading"><span><xsl:value-of select="/root/user/@name"/></span><a href="/logout">(выйти)</a></h4>
		            <form role="form" class="wrap_com" id="comment_form" method="POST" action="/?page=comment_add">
		                <input type="hidden" name="unit_id">
		                <xsl:attribute name="value">
		                    <xsl:value-of select="/root/unit" />
		                </xsl:attribute>
		                </input>
		                <input type="hidden" name="user_id">
		                <xsl:attribute name="value">
		                    <xsl:value-of select="/root/user/@id" />
		                </xsl:attribute>
		                </input>
		                <input type="hidden" name="type">
		                <xsl:attribute name="value">
		                    <xsl:value-of select="/root/user/@type" />
		                </xsl:attribute>
		                </input>
		                <textarea
		                	id="text_comment"
		                	name="comment"
		                	class="form-control comment_com"
		                	rows="3"
		                	placeholder="Можете оставить комментарий или задать вопрос..."
		                	>
		                	&#160;</textarea>
		                <input id="submit_button" class="comment-button" type="submit" />
		                <span class="symbol" id="charNum">0/5000</span>
		                <span id="resp">&#160;</span>
		            </form>
	            </div>
            </li>
        </xsl:when>
    	<xsl:otherwise>
        	<li>Для добавления комментария авторизуйтесь через соц. сети: 
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
            </li>
        </xsl:otherwise>
    </xsl:choose>
    
</xsl:template>

</xsl:stylesheet>