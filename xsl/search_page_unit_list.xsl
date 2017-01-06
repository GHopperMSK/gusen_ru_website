<?xml version="1.0"?>
<xsl:stylesheet version="1.0"
xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
<xsl:output method="xml" indent="yes" omit-xml-declaration="yes" />

<xsl:template match="root">

    <section class="category">
    <xsl:for-each select="unit">
        <article class="unit">
            <img>
                <xsl:attribute name="src">/images/tmb/<xsl:value-of select="images/img"/>
                </xsl:attribute>
                <xsl:attribute name="alt"><xsl:value-of select="manufacturer"/><xsl:text> </xsl:text><xsl:value-of select="@name"/>
                </xsl:attribute>                
            </img>
            <a>
                <xsl:attribute name="href">/unit/<xsl:value-of select="@id"/>
                </xsl:attribute>
                <h4><xsl:value-of select="manufacturer"/>&#160;<xsl:value-of select="@name"/></h4>
            </a>
            <p>Город: <xsl:value-of select="city"/></p>
            <p>Год выпуска: <xsl:value-of select="year"/></p>
            <p>Цена: <xsl:value-of select='translate(format-number(price, "###,###"),","," ")'/>&#160;₽</p>
            <xsl:if test="mileage">
            <p>Пробег: <xsl:value-of select='translate(format-number(mileage, "###,###"),","," ")'/>&#160;км.</p>
            </xsl:if>
            <xsl:if test="op_time">
            <p>Наработка: <xsl:value-of select='translate(format-number(op_time, "###,###"),","," ")'/>&#160;час.</p>
            </xsl:if>
            <p><a>
                <xsl:attribute name="href">/unit/<xsl:value-of select="@id"/>
                </xsl:attribute>Подробнее...
            </a></p>
        </article>
    </xsl:for-each>
    </section>
            
            
</xsl:template>

</xsl:stylesheet>