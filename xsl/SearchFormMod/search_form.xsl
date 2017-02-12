<?xml version="1.0"?>
<xsl:stylesheet version="1.0"
xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
<xsl:output method="xml" indent="yes" omit-xml-declaration="yes" />

<xsl:output indent="no" method="html" />

<xsl:template match="root">
	<form class="form-horizontal" method="GET" action="/">
	    <input type="hidden" name="page" >
	        <xsl:attribute name="value"><xsl:value-of select="/root/page" />
	        </xsl:attribute>
	    </input>
	    <xsl:if test="/root/page='admin'">
		    <input type="hidden" name="act" value="main" />
	    </xsl:if>
	    
	    <div class="col-lg-3 col-md-3 col-sm-6">
		    <select class="selectpicker form-control" data-size="9" id="vType" name="vType">
		        <option value="0">- Категория -</option>
		        <xsl:for-each select="categories/category">
		            <option>
		                <xsl:attribute name="value"><xsl:value-of select="@id"/>
		                </xsl:attribute>
		                <xsl:if test="@selected">
		                    <xsl:attribute name="selected">selected</xsl:attribute>
		                </xsl:if>
		                <xsl:value-of select="current()"/>
		            </option>
		        </xsl:for-each>        
		    </select>  
	    </div>
	    <div class="col-lg-3 col-md-3 col-sm-6">
		    <select class="selectpicker form-control" data-size="9" id="vManuf" name="vManuf">
		        <option value="0">- Производитель -</option>
		        <xsl:for-each select="manufacturers/manufacturer">
		            <option>
		                <xsl:attribute name="value"><xsl:value-of select="@id"/>
		                </xsl:attribute>
		                <xsl:if test="@selected">
		                    <xsl:attribute name="selected">selected</xsl:attribute>
		                </xsl:if>
		                <xsl:value-of select="current()"/>                        
		            </option>
		        </xsl:for-each>
		    </select>
	    </div>
	    <div class="col-lg-3 col-md-3 col-sm-6">
		    <select class="selectpicker form-control" data-size="9" id="vFedDistr" name="vFedDistr">
		        <option value="0">- Фед. округ -</option>
		        <xsl:for-each select="fdistricts/fdistrict">
		            <option>
		                <xsl:attribute name="value"><xsl:value-of select="@id"/>
		                </xsl:attribute>
		                <xsl:if test="@selected">
		                    <xsl:attribute name="selected">selected</xsl:attribute>
		                </xsl:if>
		                <xsl:value-of select="current()"/>                        
		            </option>
		        </xsl:for-each>
		    </select>    
		</div>
		<div class="col-lg-3 col-md-3 col-sm-6">
	    	<input class="search_button" value="Применить" type="submit" />
	    </div>
	</form>
</xsl:template>

</xsl:stylesheet>