<?xml version="1.0"?>
<xsl:stylesheet version="1.0"
xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
<xsl:output method="xml" infdent="yes" omit-xml-declaration="yes" />

<xsl:output indent="no" method="html" />
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
        	<div class="row row-comments">
            <form role="form" class="col-md-8 col-xs-12 wrap_com" id="comment_form" method="POST" action="/?page=comment_add">
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
                <textarea id="text_comment" name="comment" class="form-control comment_com" rows="3">&#160;</textarea>
                <input id="submit_button" class="comment-button" type="submit" />
                <span class="symbol" id="charNum">0/5000</span>
                <span id="resp">&#160;</span>
            </form>
            </div>
        </xsl:when>
        <xsl:otherwise>
            <!--<p>Для отправки сообщений, вам необходимо авторизироваться.</p>-->
        </xsl:otherwise>
    </xsl:choose>

    <xsl:if test="/root/comments">
        <input type="hidden" id="users_list">
        <xsl:attribute name="value">
        <xsl:for-each select="//comment[generate-id() = generate-id(key('uid', @user_id)[1])]"><xsl:value-of select="@type" />:<xsl:value-of select="@user_id" />;</xsl:for-each>
        </xsl:attribute>            
        </input>
    

		<ul class="media-list">
			<xsl:for-each select="/root/comments/comment">
				<li class="media">
					<div class="pull-left">
						<img class="media-object" src="/img/anon_user.jpg">
		                	<xsl:attribute name="user_id">
		                	<xsl:value-of select="@type" /><xsl:value-of select="@user_id" /></xsl:attribute>
						</img>
					</div>
					<div class="media-body">
						<h4 class="media-heading">
						<span>
			                <xsl:attribute name="user_id">
			                <xsl:value-of select="@type" /><xsl:value-of select="@user_id" />
			                </xsl:attribute>
			                <xsl:choose>
				                <xsl:when test="@type='fb'"><xsl:value-of select="@name" /></xsl:when>
				                <xsl:otherwise>unknown name</xsl:otherwise>
				            </xsl:choose>
						</span>
				            <a href="#"><xsl:attribute name="onclick">answer(<xsl:value-of select="@id" />,'<xsl:value-of select="@type" /><xsl:value-of select="@user_id" />')</xsl:attribute>(ответить)</a>
						</h4>
		                <xsl:choose>
		                    <xsl:when test="@approved='true'">
		                        <p><xsl:call-template name="break">
		                            <xsl:with-param name="text" select="text()" />
		                        </xsl:call-template></p>
		                    </xsl:when>
		                    <xsl:when test="@approved='false'"><p>{текст скрыт}</p></xsl:when>                    
		                </xsl:choose>
			
					    <xsl:if test="comment">
				            <xsl:for-each select="comment">
								<div class="media">
									<div class="pull-left">
										<img class="media-object" src="/img/anon_user.jpg">
						                	<xsl:attribute name="user_id">
						                	<xsl:value-of select="@type" /><xsl:value-of select="@user_id" /></xsl:attribute>
										</img>
									</div>
									<div class="media-body">
										<h4 class="media-heading">
										<span>
							                <xsl:attribute name="user_id">
							                <xsl:value-of select="@type" /><xsl:value-of select="@user_id" />
							                </xsl:attribute>
							                <xsl:choose>
								                <xsl:when test="@type='fb'"><xsl:value-of select="@name" /></xsl:when>
								                <xsl:otherwise>unknown name</xsl:otherwise>
								            </xsl:choose>
										</span>
										</h4>
						                <xsl:choose>
						                    <xsl:when test="@approved='true'">
						                        <p><xsl:call-template name="break">
						                            <xsl:with-param name="text" select="text()" />
						                        </xsl:call-template></p>
						                    </xsl:when>
						                    <xsl:when test="@approved='false'"><p>{текст скрыт}</p></xsl:when>                    
						                </xsl:choose>
							        </div>
								</div>
				            </xsl:for-each>
					    </xsl:if>
			            
					</div>
				</li>
			</xsl:for-each>
		</ul>
    </xsl:if>
</xsl:template>

</xsl:stylesheet>