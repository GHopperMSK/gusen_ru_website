<?xml version="1.0"?>
<xsl:stylesheet version="1.0"
xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
<xsl:output method="xml" indent="yes" omit-xml-declaration="yes" />

<xsl:template match="root">
        <form id="unitForm" enctype="multipart/form-data" method="POST" onsubmit="return getImages()" onreset="delImages()">
            <xsl:attribute name="action">?page=admin&amp;act=<xsl:value-of select="/root/act" />
            </xsl:attribute>
            <xsl:if test="/root/act = 'unit_edit'">
                <input type="hidden" name="id">
                <xsl:attribute name="value"><xsl:value-of select="/root/id"/>
                </xsl:attribute>
                </input>
            </xsl:if>

            <xsl:for-each select="images/img">
                <input type="hidden" name="available_images[]">
                    <xsl:attribute name="value"><xsl:value-of select="current()"/>
                    </xsl:attribute>
                </input>
            </xsl:for-each>

        
            <p>
                <select name="category" id="category">
                <option value="0">Select a category</option>
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
            </p>
            <p>
                <select id="fdistrict" name="fdistrict" onchange="fillCity(this.value)">
                <option value="0">Seelct a federal district</option>
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
            </p>
            <p>
                <select id="city" name="city">
                <option value="0">Seelct a city</option>
                <xsl:for-each select="cities/city">
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
            </p>
            <p>
                <select id="manufacturer" name="manufacturer">
                <option value="0">Seelct a manufacturer</option>
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
            </p>
            <p>
                <label for="name">Unit name:</label>
                <input type="text" id="name" name="name">
                <xsl:attribute name="value"><xsl:value-of select="/root/name"/>
                </xsl:attribute>
                </input>
                    
            </p>
            <p>
                <label for="description">Description:</label>
                <textarea id="description" name="description" rows="4" cols="50">
                <xsl:if test="not(/root/description) or /root/description=''">Description</xsl:if>
                <xsl:value-of select="/root/description"/>
                </textarea>
            </p>
            <p>
                <label for="year">Year:</label>
                <input type="number" id="year" name="year" min="1990" max="2016" step="1">
                <xsl:attribute name="value"><xsl:value-of select="/root/year"/>
                </xsl:attribute>
                </input>
            </p>
            <p>
                <label for="price">Price:</label>
                <input type="number" id="price" name="price">
                <xsl:attribute name="value"><xsl:value-of select="/root/price"/>
                </xsl:attribute>
                </input>                
            </p>
            <p>
                <label for="mileage">Mileage:</label>
                <input type="number" id="mileage" name="mileage">
                <xsl:attribute name="value"><xsl:value-of select="/root/mileage"/>
                </xsl:attribute>
                </input>                
            </p>
            <p>
                <label for="op_time">Operation time:</label>
                <input type="number" id="op_time" name="op_time">
                <xsl:attribute name="value"><xsl:value-of select="/root/op_time"/>
                </xsl:attribute>
                </input>                
            </p>
        </form>
    
        <div id="uploaded_images"><div id="del_img">&#160;</div></div>
    
        <p><input type="file" id="afile" multiple="multiple" accept="image/*"/></p>
        <p><input form="unitForm" type="submit" />
        <input form="unitForm" type="reset" /></p>
</xsl:template>

</xsl:stylesheet>




