<?xml version="1.0" encoding="utf-8"?>

<!--
 template for converting darcs' `changes` output from XML to RSS.

 the input XML must have the following structure:

 <darcs repository="">
   <changelog>
     <patch author="" date="" localdate="" inverted="" hash="">
       <name></name>
       <comment></comment>
     </patch>
   </changelog>
 </darcs>
-->
<xsl:stylesheet version="1.0"
                exclude-result-prefixes="str"
                xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
                xmlns:str="http://exslt.org/strings">

  <xsl:variable name="command">
  </xsl:variable>

  <xsl:template match="changelog">
    <rss version="2.0">
      <channel>
        <title>changes to alloc</title>
        <link><xsl:value-of select="$command"/></link>
        <description>
          Recent patches applied to the darcs repository.
        </description>

        <xsl:apply-templates>
          <xsl:sort select="@date"/>
        </xsl:apply-templates>
      </channel>
    </rss>
  </xsl:template>

  <xsl:template match="patch">
    <xsl:variable name="hash"       select="@hash"/>

    <xsl:variable name="created-as" select="/darcs/changelog/created_as"/>
    <xsl:variable name="creator-hash" select="$created-as/patch/@hash"/>
    <xsl:variable name="original-name" select="$created-as/@original_name"/>

    <xsl:variable name="annotate-href">
      <xsl:value-of select="$command"/>?c=annotate&amp;p=<xsl:value-of select="$hash"/>

      <xsl:if test="$creator-hash">&amp;ch=<xsl:value-of select="$creator-hash"/></xsl:if>
      <xsl:if test="$original-name">&amp;o=<xsl:value-of select="$original-name"/></xsl:if>
    </xsl:variable>

    <xsl:variable name="date-nodes" select="str:tokenize(@local_date, ' ')"/>
    <xsl:variable name="rfc-date">
      <xsl:value-of select="$date-nodes[1]"/>
      <xsl:value-of select="', '"/>
      <xsl:if test="$date-nodes[3] &lt; 10">0</xsl:if>
      <xsl:value-of select="$date-nodes[3]"/>
      <xsl:value-of select="' '"/>
      <xsl:value-of select="$date-nodes[2]"/>
      <xsl:value-of select="' '"/>
      <xsl:value-of select="$date-nodes[6]"/>
      <xsl:value-of select="' '"/>
      <xsl:value-of select="$date-nodes[4]"/>
      <xsl:value-of select="' '"/>
      <xsl:value-of select="$date-nodes[5]"/>
    </xsl:variable>

    <item>
      <title><xsl:value-of select="name"/></title>
      <link><xsl:value-of select="$annotate-href"/></link>
      <author><xsl:value-of select="@author"/></author>
      <description><xsl:value-of select="comment"/></description>
      <pubDate><xsl:value-of select="$rfc-date"/></pubDate>
    </item>
  </xsl:template>

  <!-- ignore <path> <created_as>, <name> and <comment> children of <patch> -->
  <xsl:template match="path"/>
  <xsl:template match="created_as"/>
  <xsl:template match="name"/>
  <xsl:template match="comment"/>
</xsl:stylesheet>
