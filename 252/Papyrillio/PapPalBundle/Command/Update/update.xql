xquery version "1.0" encoding "utf-8";
import module namespace functx = "http://www.functx.com" at "http://www.xqueryfunctions.com/xq/functx-1.0-doc-2007-01.xq";

declare namespace tei = "http://www.tei-c.org/ns/1.0";
declare namespace papy = "papyrillio";
declare option saxon:output "method=text";

(: java -Xms512m -Xmx1536m net.sf.saxon.Query -q:update.xql > import.sql :)

let $n := '&#13;&#10;'

return string-join(
for $sample in doc('sample.xml')/resultset/row
  let $id  := $sample/field[@name='id']
  let $hgv := $sample/field[@name='hgv']
  let $meta := doc(concat('http://www.papyri.info/hgv/', $hgv, '/source'))

  let $dateWhen      := $meta/tei:TEI/tei:teiHeader/tei:fileDesc/tei:sourceDesc/tei:msDesc/tei:history/tei:origin/tei:origDate[1]/@when
  let $dateNotBefore := $meta/tei:TEI/tei:teiHeader/tei:fileDesc/tei:sourceDesc/tei:msDesc/tei:history/tei:origin/tei:origDate[1]/@notBefore
  let $dateNotAfter  := $meta/tei:TEI/tei:teiHeader/tei:fileDesc/tei:sourceDesc/tei:msDesc/tei:history/tei:origin/tei:origDate[1]/@notAfter
  let $dateHgvFormat := normalize-space($meta/tei:TEI/tei:teiHeader/tei:fileDesc/tei:sourceDesc/tei:msDesc/tei:history/tei:origin/tei:origDate[1])
  let $title         := replace(normalize-space($meta/tei:TEI/tei:teiHeader/tei:fileDesc/tei:titleStmt/tei:title), "'", "\\'")
  let $material      := normalize-space($meta/tei:TEI/tei:teiHeader/tei:fileDesc/tei:sourceDesc/tei:msDesc/tei:physDesc/tei:objectDesc/tei:supportDesc/tei:support/tei:material)
  let $keywords      := replace(normalize-space(string-join($meta/tei:TEI/tei:teiHeader/tei:profileDesc/tei:textClass/tei:keywords[@scheme='hgv']/tei:term, ', ')), "'", "\\'")
  let $digitalImages := replace(string-join($meta/tei:TEI/tei:text/tei:body/tei:div[@type='figure']/tei:p/tei:figure/tei:graphic/@url, ', '), "'", "\\'")
  let $provenance    := replace(normalize-space($meta/tei:TEI/tei:teiHeader/tei:fileDesc/tei:sourceDesc/tei:msDesc/tei:history/tei:origin/tei:origPlace), "'", "\\'")

  let $dateSort      := if($dateWhen) then $dateWhen else (if ($dateNotBefore) then $dateNotBefore else $dateNotAfter)
  let $dateSign      := if (starts-with($dateSort, '-')) then '-' else ''
  let $dateSort      := if (starts-with($dateSort, '-')) then substring($dateSort, 2) else $dateSort

  let $dateYear      := substring($dateSort, 1, 4)
  let $dateMonth     := substring($dateSort, 6, 2)
  let $dateDay       := substring($dateSort, 9, 2)

  let $dateYear      := if ($dateYear) then $dateYear else '0000'
  let $dateMonth     := if ($dateMonth) then (if ($dateSign = '-') then functx:pad-integer-to-length(13 - number($dateMonth), 2) else $dateMonth) else '00'
  let $dateDay       := if ($dateDay) then (if ($dateSign = '-') then functx:pad-integer-to-length(31 - number($dateDay), 2) else $dateDay) else '00'

  let $dateSort      := concat($dateSign, $dateYear, $dateMonth, $dateDay)

  return concat('UPDATE sample SET ',
    'dateWhen = ', if($dateWhen) then concat("'", $dateWhen, "'") else 'NULL', ", ",
    'dateNotBefore = ', if($dateNotBefore) then concat("'", $dateNotBefore, "'") else 'NULL', ", ",
    'dateNotAfter = ', if($dateNotAfter) then concat("'", $dateNotAfter, "'") else 'NULL', ", ",
    'dateHgvFormat = ', if($dateHgvFormat) then concat("'", $dateHgvFormat, "'") else 'NULL', ", ",
    'title = ', if($title) then concat("'", $title, "'") else 'NULL', ", ",
    'material = ', if($material) then concat("'", $material, "'") else 'NULL', ", ",
    'keywords = ', if($keywords) then concat("'", $keywords, "'") else 'NULL', ", ",
    'digitalImages = ', if($digitalImages) then concat("'", $digitalImages, "'") else 'NULL', ", ",
    'provenance = ', if($provenance) then concat("'", $provenance, "'") else 'NULL', ", ",
    'dateSort = ', if($dateSort) then $dateSort else 'NULL',
    ' WHERE id = ', $id, " AND hgv = '", $hgv, "';", $n), '')
