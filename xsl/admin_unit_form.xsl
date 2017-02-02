<?xml version="1.0"?>
<xsl:stylesheet version="1.0"
xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
<xsl:output method="xml" indent="yes" omit-xml-declaration="yes" />

<xsl:template match="root">

    <form class="form-horizontal" id="unitForm" enctype="multipart/form-data" method="POST" onsubmit="return getImages()" onreset="delImages()">
        <xsl:attribute name="action">?page=admin&amp;act=<xsl:value-of select="/root/act" />
        </xsl:attribute>
        <xsl:if test="/root/act = 'unit_edit'">
            <input type="hidden" name="id">
            <xsl:attribute name="value"><xsl:value-of select="/root/id"/>
            </xsl:attribute>
            </input>
        </xsl:if>
		<fieldset>
			<legend>
				<xsl:choose>
					<xsl:when test="/root/act = 'unit_edit'">
						Editing the unit
					</xsl:when>
					<xsl:otherwise>
						Adding a new unit
					</xsl:otherwise>
				</xsl:choose>
			</legend>

        <xsl:for-each select="images/img">
            <input type="hidden" name="available_images[]">
                <xsl:attribute name="value"><xsl:value-of select="current()"/>
                </xsl:attribute>
            </input>
        </xsl:for-each>

        <div class="form-group">
            <div class="col-md-12">
                <select class="selectpicker form-control" name="owner" id="owner">
                    <option value="0">Select an owner</option>
                    <xsl:for-each select="owners/owner">
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
        </div>

        <div class="form-group">
            <div class="col-md-12">
                <select class="selectpicker form-control" name="category" id="category">
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
            </div>
        </div>
        <div class="form-group">
            <div class="col-md-12">
                <select class="selectpicker form-control" id="fdistrict" name="fdistrict">
                <option value="0">Select a federal district</option>
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
        </div>
        <div class="form-group">
            <div class="col-md-12">
                <select class="selectpicker form-control" id="city" name="city">
                <option value="0">Select a city</option>
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
            </div>
        </div>
        <div class="form-group">
            <div class="col-md-12">
                <select class="selectpicker form-control" id="manufacturer" name="manufacturer">
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
            </div>
        </div>
        <div class="form-group">
            <label class="control-label col-md-1" for="name">Unit name:</label>
            <div class="col-md-11">
                <input class="form-control" placeholder="Unit name" type="text" id="name" name="name">
	                <xsl:attribute name="value"><xsl:value-of select="/root/name" />
	                </xsl:attribute>
                </input>
            </div>
        </div>
        <div class="form-group">
            <label class="control-label col-md-1" for="description">Description:</label>
            <div class="col-md-11">
                <textarea class="form-control" placeholder="Unit description" id="description" name="description" rows="4" cols="50">
                <xsl:if test="not(/root/description) or /root/description=''">Description</xsl:if>
                <xsl:value-of select="/root/description"/>
                </textarea>
        	</div>
        </div>
        <div class="form-group">
            <label class="control-label col-md-1" for="year">Year:</label>
            <div class="col-md-11">                
                <input class="form-control" placeholder="Year of issue" type="number" id="year" name="year" min="1990" max="2016" step="1">
                <xsl:attribute name="value"><xsl:value-of select="/root/year"/>
                </xsl:attribute>
                </input>
             </div>
        </div>
        <div class="form-group">
            <label class="control-label col-md-1" for="price">Price:</label>
            <div class="col-md-11">                
                <input class="form-control" placeholder="Price of the unit" type="number" id="price" name="price">
                <xsl:attribute name="value"><xsl:value-of select="/root/price"/>
                </xsl:attribute>
                </input>
            </div>
        </div>
        <div class="form-group">
            <label class="control-label col-md-1" for="mileage">Mileage:</label>
            <div class="col-md-11">                
	            <input class="form-control" placeholder="Mileage of the unit" type="number" id="mileage" name="mileage">
                <xsl:attribute name="value"><xsl:value-of select="/root/mileage"/>
                </xsl:attribute>
                </input>
            </div>
        </div>
        <div class="form-group">
            <label class="control-label col-md-1" for="op_time">Op. time:</label>
            <div class="col-md-11">                
                <input class="form-control" placeholder="Operation time of the unit" type="number" id="op_time" name="op_time">
                <xsl:attribute name="value"><xsl:value-of select="/root/op_time"/>
                </xsl:attribute>
                </input>
            </div>
        </div>
	</fieldset>
    </form>

    <div class="form-group" id="uploaded_images">
    	<div class="col-md-12" id="del_img">DROP AN IMAGE HERE TO DELETE IT</div>
    </div>

    <form class="form-horizontal">
        <div class="form-group">
            <div class="col-md-12">
    		    <input class="form-control" type="file" id="afile" multiple="multiple" accept="image/*" />
    	    </div>
    	</div>
        <div class="form-group">
            <div class="col-md-12">
                <input class="btn btn-default btn-md col-md-4" form="unitForm" type="submit" />
                <input class="btn btn-default btn-md col-md-4 col-md-offset-4" form="unitForm" type="reset" />
            </div>
        </div>
    </form>
</xsl:template>

</xsl:stylesheet>




