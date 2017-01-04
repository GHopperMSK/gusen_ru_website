<?xml version="1.0"?>
<xsl:stylesheet version="1.0"
xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
<xsl:output method="xml" indent="yes" omit-xml-declaration="yes" />

<xsl:template match="root">
    <xsl:for-each select="category">
        <xsl:if test="node()">
            <h3>
                <a>
                    <xsl:attribute name="href">/search/<xsl:value-of select="@id"/>
                    </xsl:attribute>
                    <xsl:value-of select="@name"/>
                </a>
            </h3>
            <section class="category">
                <xsl:for-each select="unit">
                    <article class="unit">
                        <img>
                            <xsl:attribute name="alt"><xsl:value-of select="manufacturer"/><xsl:text> </xsl:text><xsl:value-of select="@name"/>
                            </xsl:attribute>
                            <xsl:attribute name="src">images/tmb/<xsl:value-of select="img"/>
                            </xsl:attribute>
                        </img>
                        <a>
                            <xsl:attribute name="href">/unit/<xsl:value-of select="@id"/>
                            </xsl:attribute>
                            <h4><xsl:value-of select="manufacturer"/>&#160;<xsl:value-of select="@name"/></h4>
                        </a>
                        <p>Город: <xsl:value-of select="city"/></p>
                        <p>Год выпуска: <xsl:value-of select="year"/></p>
                        
                        <xsl:if test="mileage">
                        <p>Пробег: <xsl:value-of select='translate(format-number(mileage, "###,###"),","," ")'/>&#160;км.</p>
                        </xsl:if>
                        <xsl:if test="op_time">
                        <p>Наработка: <xsl:value-of select='translate(format-number(op_time, "###,###"),","," ")'/>&#160;час.</p>
                        </xsl:if>
                        
                        <p>Цена: <xsl:value-of select='translate(format-number(price, "###,###"),","," ")'/>&#160;₽</p>
                        <p><a>
                            <xsl:attribute name="href">unit/<xsl:value-of select="@id"/>
                            </xsl:attribute>Подробнее...
                        </a></p>
                    </article>
                </xsl:for-each>
            </section>

            <xsl:if test="count(*) = 4">
            <h5 style="text-align: right;">
                <a>
                    <xsl:attribute name="href">/search/<xsl:value-of select="@id"/>
                    </xsl:attribute>Все предложения из категории
                </a>
            </h5>
            </xsl:if>            

        </xsl:if>
    </xsl:for-each>
</xsl:template>

</xsl:stylesheet>