<?xml version="1.0"?>
<xsl:stylesheet version="1.0"
xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
<xsl:output method="xml" indent="yes" omit-xml-declaration="yes" />

<xsl:template match="root">
    <form class="form-horizontal" method="POST">
        <xsl:attribute name="action">
        	<xsl:choose>
				<xsl:when test="owner">?page=admin&amp;act=owner_edit</xsl:when>
				<xsl:otherwise>?page=admin&amp;act=owner_add</xsl:otherwise>
			</xsl:choose>
        </xsl:attribute>
		<xsl:if test="owner">
			<input type="hidden" name="id">
		        <xsl:attribute name="value">
		        	<xsl:value-of select="owner/@id" />
				</xsl:attribute>
			</input>
		</xsl:if>
		<fieldset>
			<legend>
				<xsl:choose>
					<xsl:when test="owner">
						Editing the owner
					</xsl:when>
					<xsl:otherwise>
						Adding a new owner
					</xsl:otherwise>
				</xsl:choose>
			</legend>
		</fieldset>

        <div class="form-group">
            <label class="control-label col-md-1" for="name">Owner name:</label>
            <div class="col-md-11">
                <input class="form-control" placeholder="Owner name" type="text" id="name" name="name">
	                <xsl:attribute name="value"><xsl:value-of select="owner/@name" />
	                </xsl:attribute>
                </input>
            </div>
        </div>
        <div class="form-group">
            <label class="control-label col-md-1" for="description">Description:</label>
            <div class="col-md-11">
                <textarea class="form-control" placeholder="Unit description" id="description" name="description" rows="15">
	        	<xsl:choose>
					<xsl:when test="not(owner) or owner=''">Description</xsl:when>
					<xsl:otherwise><xsl:value-of select="owner" /></xsl:otherwise>
				</xsl:choose>
                </textarea>
        	</div>
        </div>

        <div class="form-group">
            <div class="col-md-12">
                <input class="btn btn-default btn-md col-md-4" type="submit" />
                <input class="btn btn-default btn-md col-md-4 col-md-offset-4" type="reset" />
            </div>
        </div>
    </form>
</xsl:template>

</xsl:stylesheet>




