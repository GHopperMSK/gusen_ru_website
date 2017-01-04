<?xml version="1.0"?>
<xsl:stylesheet version="1.0"
xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
<xsl:output method="xml" indent="yes" omit-xml-declaration="yes" />

<xsl:template match="root">
<form method="GET" action="/">
    <input type="hidden" name="page" >
        <xsl:attribute name="value"><xsl:value-of select="/root/page" />
        </xsl:attribute>
    </input>
    <xsl:if test="/root/page='admin'">
    <input type="hidden" name="act" value="main" />
    
    </xsl:if>
    
    <select id="vType" name="vType">
        <option value="0">- категория не выбрана -</option>
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
    <select id="vManuf" name="vManuf">
        <option value="0">- производитель не выбран -</option>
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
    <select id="vFedDistr" name="vFedDistr">
        <option value="0">- фед.округ не выбран -</option>
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

    <input type="submit" />
</form>
</xsl:template>

</xsl:stylesheet>