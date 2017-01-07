<?xml version="1.0"?>
<xsl:stylesheet version="1.0"
xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
<xsl:output method="xml" infdent="yes" omit-xml-declaration="yes" />

<xsl:key name="uid" match="comment" use="@user_id"/>
<xsl:key name="sub_uid" match="sub_comment" use="@user_id"/>

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

    <xsl:choose>
        <xsl:when test="user">
            <form id="comment_form" method="POST" action="/?page=comment_add">
                <input type="hidden" name="unit_id">
                <xsl:attribute name="value">
                    <xsl:value-of select="/root/unit_id" />
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
                <textarea id="text_comment" name="comment">&#160;</textarea>
                <input id="submit_button" type="submit" />
                <span id="charNum">0/5000</span>
                <span id="resp">&#160;</span>
            </form> 
        </xsl:when>
        <xsl:otherwise>
            <p>Для отправки сообщений, вам необходимо авторизироваться.</p>
        </xsl:otherwise>
    </xsl:choose>
    <br />
    <xsl:if test="/root/comments">
        <input type="hidden" id="users_list">
        <xsl:attribute name="value">
        <xsl:for-each select="//comment[generate-id() = generate-id(key('uid', @user_id)[1])]"><xsl:value-of select="@type" />:<xsl:value-of select="@user_id" />;</xsl:for-each>
        </xsl:attribute>            
        </input>
    
        <xsl:for-each select="/root/comments/comment">
        <div class="comment">
            <p>
                <xsl:attribute name="user_id">
                <xsl:value-of select="@type" /><xsl:value-of select="@user_id" />
                </xsl:attribute>
                <xsl:if test="@type='fb'">
                    <xsl:value-of select="@name" />
                </xsl:if>
            </p>
            <p>
                <xsl:attribute name="id"><xsl:value-of select="@id" /></xsl:attribute>
                <xsl:choose>
                    <xsl:when test="@approved='true'">
                        <xsl:call-template name="break">
                            <xsl:with-param name="text" select="text()" />
                        </xsl:call-template><br />
                    </xsl:when>
                    <xsl:when test="@approved='false'">
                    <xsl:attribute name="class">discarded</xsl:attribute>
                    {текст скрыт}
                    </xsl:when>                    
                </xsl:choose>
            </p>
            <a href="#a">
            <xsl:attribute name="onclick">answer(<xsl:value-of select="@id" />,'<xsl:value-of select="@type" /><xsl:value-of select="@user_id" />')</xsl:attribute>
            (ответить)
            </a>
            <xsl:for-each select="comment">
                <p style="margin-left: 20px;">
                    <xsl:attribute name="user_id">
                    <xsl:value-of select="@type" /><xsl:value-of select="@user_id" />
                    </xsl:attribute>
                    <xsl:if test="@type='fb'">
                        <xsl:value-of select="@name" />
                    </xsl:if>
                </p>
                <p style="margin-left: 20px;">
                <xsl:attribute name="id"><xsl:value-of select="@id" /></xsl:attribute>
                <xsl:choose>
                    <xsl:when test="@approved='true'">
                        <xsl:call-template name="break">
                            <xsl:with-param name="text" select="text()" />
                        </xsl:call-template><br />
                    </xsl:when>
                    <xsl:when test="@approved='false'">
                    <xsl:attribute name="class">discarded</xsl:attribute>
                    {текст скрыт}
                    </xsl:when>                    
                </xsl:choose>
                </p>
            </xsl:for-each>
        </div>
        
        </xsl:for-each>
    </xsl:if>
</xsl:template>

</xsl:stylesheet>