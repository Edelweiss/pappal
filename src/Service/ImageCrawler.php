<?php

namespace App\Service;

use Exception;

class ImageCrawler{
  public $images        = array();
  public $type          = null;
  public $ddb           = null;
  public $url           = null;
  public $urlProtocol   = null;
  public $urlHost       = null;
  public $urlPath       = null;
  public $urlParameters = null;

  public static $TYPE_LIST = array(
    'ipap'     => 'ipap.csad.ox.ac.uk',
    'csad'     => 'www.csad.ox.ac.uk',
    'apis'     => 'columbia.edu',
    'digibib'  => 'digibib.ub.uni-giessen.de',
    'pop'      => 'dl.uni-leipzig.de',
    'gem'      => 'globalegyptianmuseum.org',
    'graz'     => 'uni-graz.at',
    'sceti'    => 'sceti.library.upenn.edu',
    'onb'      => 'aleph.onb.ac.at',
    'hollis'   => 'hollisclassic.harvard.edu',
    'laurenz'  => 'bml.firenze.sbn.it',
    'koeln'    => 'uni-koeln.de',
    'duke'     => 'scriptorium.lib.duke.edu',
    'hdgrad'   => 'rzuser.uni-heidelberg.de/~gv0/Papyri/Grad.html',
    'hd'       => 'rzuser.uni-heidelberg.de/~gv0',
    'lib'      => 'library.case.edu',
    'agate'    => 'archaeogate.org',
    'petri'    => 'petriecat.museums.ucl.ac.uk',
    'hum'      => 'hum.ku.dk',
    'sorbonne' => 'papyrologie.paris4.sorbonne.fr',
    'leip'     => 'pcclu07.rz.uni-leipzig.de',
    'pcount'   => 'pcount.arts.kuleuven.ac.be',
    'warsaw'   => 'papyrology.uw.edu.pl',
    'enri'     => 'enriqueta.man.ac.uk',
    'dpg'      => 'dpg.lib.berkeley.edu',
    'ville'    => 'ville-ge.ch',
    'rmn'      => 'photo.rmn.fr',
    'trier'    => 'digipap.uni-trier.de',
    'librit'   => 'librit.unibo.it',
    'nbno'     => 'nb.no',
    'oxy'      => '163.1.169.40/cgi-bin/library',
    'dendlon'  => 'dendlon.de',
    'wash'     => 'library.wustl.edu',
    'ucl'      => 'ucl.ac.uk',
    'glas'     => 'special.lib.gla.ac.uk',
    'yale'     => 'beinecke.library.yale.edu',
    'bulow'    => 'igl.ku.dk',
    'misha'    => 'www.misha.fr',
    'ifao'     => 'www.ifao.egnet.net',
    'psio'     => 'psi-online.it'
  );

  public static $TYPE_MODE_URL = array('petri', 'trier', 'bulow', 'lib', 'ville');
  
  public static $CSAD_APPLICATION_NEEDED = array(
    'http://ipap.csad.ox.ac.uk/4DLink4/4DACTION/IPAPwebquery?vPub=P.Mich.&vVol=4&vNum=223'
  );

  public static $ONB_MISSING_IMAGE_LIST = array(
    'sb;1;5240 / 13987' => 'http://aleph.onb.ac.at/F/?func=find-c&ccl_term=WID%3DRZ00001015&local_base=ONB08',
    'sb;1;5238 / 13985' => 'http://aleph.onb.ac.at/F/?func=find-c&ccl_term=WID%3DRZ00001030&local_base=ONB08',
    'cpr;15;1 / 9899' => 'http://aleph.onb.ac.at/F/?func=find-c&ccl_term=WID%3DRZ00001042&local_base=ONB08',
    'sb;1;5231qtpl / 13979' => 'http://aleph.onb.ac.at/F/?func=find-c&ccl_term=WID%3DRZ00001043&local_base=ONB08',
    'cpr;15;7 / 9919' => 'http://aleph.onb.ac.at/F/?func=find-c&ccl_term=WID%3DRZ00002053&local_base=ONB08',
    'p.rain.cent;;40 / 5262' => 'http://aleph.onb.ac.at/F/?func=find-c&ccl_term=WID%3DRZ00002329&local_base=ONB08',
    'p.rain.cent;;41 / 5263' => 'http://aleph.onb.ac.at/F/?func=find-c&ccl_term=WID%3DRZ00002330&local_base=ONB08',
    'p.rain.cent;;48 / 5267' => 'http://aleph.onb.ac.at/F/?func=find-c&ccl_term=WID%3DRZ00002337&local_base=ONB08',
    'p.rain.cent;;53 / 5268' => 'http://aleph.onb.ac.at/F/?func=find-c&ccl_term=WID%3DRZ00002342&local_base=ONB08',
    'p.rain.cent;;55 / 5269' => 'http://aleph.onb.ac.at/F/?func=find-c&ccl_term=WID%3DRZ00002344&local_base=ONB08',
    'p.vind.tand;;12 / 13681' => 'http://aleph.onb.ac.at/F/?func=find-c&ccl_term=WID%3DRZ00004343&local_base=ONB08',
    'p.paramone;;6 / 78703' => 'http://aleph.onb.ac.at/F/?func=find-c&ccl_term=WID%3DRZ00004661&local_base=ONB08',
    // 2nd run
    '9881' => 'http://aleph.onb.ac.at/F/?func=find-c&ccl_term=WID%3DRZ00003548&local_base=ONB08',
    '9882' => 'http://aleph.onb.ac.at/F/?func=find-c&ccl_term=WID%3DRZ00003550&local_base=ONB08',
    '9883' => 'http://aleph.onb.ac.at/F/?func=find-c&ccl_term=WID%3DRZ00002213&local_base=ONB08',
    '9886' => 'http://aleph.onb.ac.at/F/?func=find-c&ccl_term=WID%3DRZ00003557&local_base=ONB08',
    '9890' => 'http://aleph.onb.ac.at/F/?func=find-c&ccl_term=WID%3DRZ00003755&local_base=ONB08',
    '9892' => 'http://aleph.onb.ac.at/F/?func=find-c&ccl_term=WID%3DRZ00003758&local_base=ONB08',
    '12853' => 'http://aleph.onb.ac.at/F/?func=find-c&ccl_term=WID%3DRZ00004114&local_base=ONB08',
    '15004' => 'http://aleph.onb.ac.at/F/?func=find-c&ccl_term=WID%3DRZ00004568&local_base=ONB08',
    '15430' => 'http://aleph.onb.ac.at/F/?func=find-c&ccl_term=WID%3DRZ00002371&local_base=ONB08',
    '15435' => 'http://aleph.onb.ac.at/F/?func=find-c&ccl_term=WID%3DRZ00002377&local_base=ONB08',
    '15436' => 'http://aleph.onb.ac.at/F/?func=find-c&ccl_term=WID%3DRZ00002378&local_base=ONB08',
    '15444' => 'http://aleph.onb.ac.at/F/?func=find-c&ccl_term=WID%3DRZ00002386&local_base=ONB08',
    '15447' => 'http://aleph.onb.ac.at/F/?func=find-c&ccl_term=WID%3DRZ00002391&local_base=ONB08',
    '15452' => 'http://aleph.onb.ac.at/F/?func=find-c&ccl_term=WID%3DRZ00002397&local_base=ONB08',
    '15453' => 'http://aleph.onb.ac.at/F/?func=find-c&ccl_term=WID%3DRZ00002398&local_base=ONB08',
    '15457' => 'http://aleph.onb.ac.at/F/?func=find-c&ccl_term=WID%3DRZ00004334&local_base=ONB08',
    '15834' => 'http://aleph.onb.ac.at/F/?func=find-c&ccl_term=WID%3DRZ00003417&local_base=ONB08',
    '15841' => 'http://aleph.onb.ac.at/F/?func=find-c&ccl_term=WID%3DRZ00003458&local_base=ONB08',
    '15843' => 'http://aleph.onb.ac.at/F/?func=find-c&ccl_term=WID%3DRZ00003460&local_base=ONB08',
    '15844' => 'http://aleph.onb.ac.at/F/?func=find-c&ccl_term=WID%3DRZ00003462&local_base=ONB08',
    '15845' => 'http://aleph.onb.ac.at/F/?func=find-c&ccl_term=WID%3DRZ00003464&local_base=ONB08',
    '16036' => 'http://aleph.onb.ac.at/F/?func=find-c&ccl_term=WID%3DRZ00002529&local_base=ONB08',
    '17171' => 'http://aleph.onb.ac.at/F/?func=find-c&ccl_term=WID%3DRZ00004318&local_base=ONB08',
    '17292' => 'http://aleph.onb.ac.at/F/?func=find-c&ccl_term=WID%3DRZ00004258&local_base=ONB08',
    '18734' => 'http://aleph.onb.ac.at/F/?func=find-c&ccl_term=WID%3DRZ00001654&local_base=ONB08',
    '18739' => 'http://aleph.onb.ac.at/F/?func=find-c&ccl_term=WID%3DRZ00001666&local_base=ONB08',
    '20829' => 'http://aleph.onb.ac.at/F/?func=find-c&ccl_term=WID%3DRZ00002617&local_base=ONB08',
    '20838' => 'http://aleph.onb.ac.at/F/?func=find-c&ccl_term=WID%3DRZ00003614&local_base=ONB08',
    '20842' => 'http://aleph.onb.ac.at/F/?func=find-c&ccl_term=WID%3DRZ00001799&local_base=ONB08',
    '20846' => 'http://aleph.onb.ac.at/F/?func=find-c&ccl_term=WID%3DRZ00003618&local_base=ONB08',
    '20856' => 'http://aleph.onb.ac.at/F/?func=find-c&ccl_term=WID%3DRZ00003646&local_base=ONB08',
    '20857' => 'http://aleph.onb.ac.at/F/?func=find-c&ccl_term=WID%3DRZ00003647&local_base=ONB08',
    '20860' => 'http://aleph.onb.ac.at/F/?func=find-c&ccl_term=WID%3DRZ00002362&local_base=ONB08',
    '20871' => 'http://aleph.onb.ac.at/F/?func=find-c&ccl_term=WID%3DRZ00003741&local_base=ONB08',
    '20878' => 'http://aleph.onb.ac.at/F/?func=find-c&ccl_term=WID%3DRZ00003753&local_base=ONB08',
    '20879' => 'http://aleph.onb.ac.at/F/?func=find-c&ccl_term=WID%3DRZ00003759&local_base=ONB08',
    '20883' => 'http://aleph.onb.ac.at/F/?func=find-c&ccl_term=WID%3DRZ00001953&local_base=ONB08',
    '20884' => 'http://aleph.onb.ac.at/F/?func=find-c&ccl_term=WID%3DRZ00001979&local_base=ONB08',
    '36843' => 'http://aleph.onb.ac.at/F/?func=find-c&ccl_term=WID%3DRZ00003585&local_base=ONB08',
    '37582' => 'http://aleph.onb.ac.at/F/?func=find-c&ccl_term=WID%3DRZ00000300&local_base=ONB08',
    '38897' => 'http://aleph.onb.ac.at/F/?func=find-c&ccl_term=WID%3DRZ00004419&local_base=ONB08',
    '38988' => 'http://aleph.onb.ac.at/F/?func=find-c&ccl_term=WID%3DRZ00004593&local_base=ONB08',
    '39889' => 'http://aleph.onb.ac.at/F/?func=find-c&ccl_term=WID%3DRZ00004565&local_base=ONB08',
    '40956' => 'http://aleph.onb.ac.at/F/?func=find-c&ccl_term=WID%3DRZ00002376&local_base=ONB08',
    '40960' => 'http://aleph.onb.ac.at/F/?func=find-c&ccl_term=WID%3DRZ00004368&local_base=ONB08',
    '40962' => 'http://aleph.onb.ac.at/F/?func=find-c&ccl_term=WID%3DRZ00003467&local_base=ONB08',
    '41077' => 'http://aleph.onb.ac.at/F/?func=find-c&ccl_term=WID%3DRZ00003744&local_base=ONB08',
    '41083' => 'http://aleph.onb.ac.at/F/?func=find-c&ccl_term=WID%3DRZ00003754&local_base=ONB08',
    '41085' => 'http://aleph.onb.ac.at/F/?func=find-c&ccl_term=WID%3DRZ00003760&local_base=ONB08',
    '41090' => 'http://aleph.onb.ac.at/F/?func=find-c&ccl_term=WID%3DRZ00001968&local_base=ONB08',
    '65484' => 'http://aleph.onb.ac.at/F/?func=find-c&ccl_term=WID%3DRZ00004099&local_base=ONB08',
    '78191' => 'http://aleph.onb.ac.at/F/?func=find-c&ccl_term=WID%3DRZ00002239&local_base=ONB08',
    '78712' => 'http://aleph.onb.ac.at/F/?func=find-c&ccl_term=WID%3DRZ00004663&local_base=ONB08'
  );

  public static $POP_MISSING_IMAGE_LIST = array(
    'http://papyri-leipzig.dl.uni-leipzig.de/receive/UBLPapyri_schrift_00002380',
    'http://papyri-leipzig.dl.uni-leipzig.de/receive/UBLPapyri_schrift_00002370',
    'http://papyri-leipzig.dl.uni-leipzig.de/receive/UBLPapyri_schrift_00002350',
    'http://papyri-leipzig.dl.uni-leipzig.de/receive/UBLPapyri_schrift_00002360',
    'http://papyri-leipzig.dl.uni-leipzig.de/receive/IAwJPapyri_schrift_00000090',
    // 2nd run
    'http://papyri-leipzig.dl.uni-leipzig.de/receive/UBLPapyri_schrift_00000970', // even server error
    'http://papyri-leipzig.dl.uni-leipzig.de/receive/UBLPapyri_schrift_00000330',
    'http://papyri-leipzig.dl.uni-leipzig.de/receive/UBLPapyri_schrift_00000580',
    'http://papyri-leipzig.dl.uni-leipzig.de/receive/UBLPapyri_schrift_00000850'
  );

  public static $OXY_MISSING_IMAGE_LIST = array(

  );

  public static $GRAZ_MISSING_IMAGE_LIST = array(
    'sb;16;12484 / 16249' => 'http://www.uni-graz.at/ub/sosa/katalog/katalogisate/1942.html'
  );

  public static $DIGIBIB_MISSING_IMAGE_LIST = array(
    'http://digibib.ub.uni-giessen.de/cgi-bin/populo/pap.pl?t_allegro=x&f_SIG=SB+12948',
    'http://digibib.ub.uni-giessen.de/cgi-bin/populo/pap.pl?t_allegro=x&f_SIG=P.+Iand.+49',
    'http://digibib.ub.uni-giessen.de/cgi-bin/populo/pap.pl?t_allegro=x&f_SIG=SB+12948'
  );

  public static $APIS_MISSING_IMAGE_LIST = array(
    // searched for with a script
    'p.tebt;3.2;866 / 5428' => 'http://wwwapp.cc.columbia.edu/ldpd/app/apis/item?mode=item&key=berkeley.apis.11512', // item not found
    'p.tebt;3.2;846 / 5417' => 'http://wwwapp.cc.columbia.edu/ldpd/app/apis/item?mode=item&key=berkeley.apis.1065', // item not found
    'p.tebt;3.1;775 / 5366' => 'http://wwwapp.cc.columbia.edu/ldpd/app/apis/item?mode=item&key=berkeley.apis.997', // item not found
    'p.tebt;1;72 / 3708a' => 'http://wwwapp.cc.columbia.edu/ldpd/app/apis/item?mode=item&key=berkeley.apis.455', // item not found

    'p.yale;1;30 / 8272' => 'http://wwwapp.cc.columbia.edu/ldpd/app/apis/item?mode=item&key=yale.apis.0000240000',
    'p.yale;1;29 / 8214' => 'http://wwwapp.cc.columbia.edu/ldpd/app/apis/item?mode=item&key=yale.apis.0021920000',
    'p.chic.haw;;7c / 8615' => 'http://www.columbia.edu/cgi-bin/cul/apis/apis?mode=item&key=chicago.apis.2',
    'p.tebt;3.2;940 / 5465' => 'http://wwwapp.cc.columbia.edu/ldpd/app/apis/item?mode=item&key=berkeley.apis.1257',
    'p.tebt;3.1;704 / 5316' => 'http://wwwapp.cc.columbia.edu/ldpd/app/apis/item?mode=item&key=berkeley.apis.222',
    'p.yale;1;49 / 8275' => 'http://wwwapp.cc.columbia.edu/ldpd/app/apis/item?mode=item&key=yale.apis.0000270000',
    'p.tebt;3.2;1061 / 5521' => 'http://wwwapp.cc.columbia.edu/ldpd/app/apis/item?mode=item&key=berkeley.apis.1215',
    'p.tebt;3.1;770 / 5363' => 'http://wwwapp.cc.columbia.edu/ldpd/app/apis/item?mode=item&key=berkeley.apis.992',
    'p.tebt;3.1;794 / 5380' => 'http://wwwapp.cc.columbia.edu/ldpd/app/apis/item?mode=item&key=berkeley.apis.1019',
    'p.tebt;3.2;884 / 4406' => 'http://wwwapp.cc.columbia.edu/ldpd/app/apis/item?mode=item&key=berkeley.apis.1165',
    'o.mich;1;1 / 41864a' => 'http://wwwapp.cc.columbia.edu/ldpd/app/apis/item?mode=item&key=michigan.apis.300',
    'o.mich;1;1 / 41864b' => 'http://wwwapp.cc.columbia.edu/ldpd/app/apis/item?mode=item&key=michigan.apis.300',
    'p.tebt;3.2;885 / 5438' => 'http://wwwapp.cc.columbia.edu/ldpd/app/apis/item?mode=item&key=berkeley.apis.1166',
    'p.tebt;3.2;879 / 5434' => 'http://wwwapp.cc.columbia.edu/ldpd/app/apis/item?mode=item&key=berkeley.apis.1162',
    'p.yale;1;36 / 6204' => 'http://wwwapp.cc.columbia.edu/ldpd/app/apis/item?mode=item&key=yale.apis.0016470032',
    'p.yale;1;43 / 5536' => 'http://wwwapp.cc.columbia.edu/ldpd/app/apis/item?mode=item&key=yale.apis.0016440000',
    'p.yale;1;44 / 5537' => 'http://wwwapp.cc.columbia.edu/ldpd/app/apis/item?mode=item&key=yale.apis.0016450000',
    'p.tebt;3.1;774 / 5365' => 'http://wwwapp.cc.columbia.edu/ldpd/app/apis/item?mode=item&key=berkeley.apis.996',
    'p.tebt;3.2;916 / 5452' => 'http://wwwapp.cc.columbia.edu/ldpd/app/apis/item?mode=item&key=berkeley.apis.1191',
    'p.yale;1;51 / 2974' => 'http://wwwapp.cc.columbia.edu/ldpd/app/apis/item?mode=item&key=yale.apis.0002370000',
    'p.tebt;3.1;793 / 5379' => 'http://wwwapp.cc.columbia.edu/ldpd/app/apis/item?mode=item&key=berkeley.apis.1018',
    'p.tebt;3.2;886 / 5439' => 'http://wwwapp.cc.columbia.edu/ldpd/app/apis/item?mode=item&key=berkeley.apis.1167',
    'p.tebt;3.2;829 / 5405' => 'http://wwwapp.cc.columbia.edu/ldpd/app/apis/item?mode=item&key=berkeley.apis.1223',
    'p.tebt;3.2;1024 / 5507' => 'http://wwwapp.cc.columbia.edu/ldpd/app/apis/item?mode=item&key=berkeley.apis.1069',
    'p.tebt;3.1;779 / 5367' => 'http://wwwapp.cc.columbia.edu/ldpd/app/apis/item?mode=item&key=berkeley.apis.1000',
    'p.tebt;3.2;945 / 5466' => 'http://wwwapp.cc.columbia.edu/ldpd/app/apis/item?mode=item&key=berkeley.apis.1261',
    'p.tebt;3.2;852 / 5421' => 'http://wwwapp.cc.columbia.edu/ldpd/app/apis/item?mode=item&key=berkeley.apis.1071',
    'p.tebt;3.1;780 / 5368' => 'http://wwwapp.cc.columbia.edu/ldpd/app/apis/item?mode=item&key=berkeley.apis.1001',
    'p.tebt;3.2;856 / 5424' => 'http://wwwapp.cc.columbia.edu/ldpd/app/apis/item?mode=item&key=berkeley.apis.1075',
    'p.tebt;3.2;1043 / 5516' => 'http://wwwapp.cc.columbia.edu/ldpd/app/apis/item?mode=item&key=berkeley.apis.1222',
    'p.tebt;3.2;909 / 5447' => 'http://wwwapp.cc.columbia.edu/ldpd/app/apis/item?mode=item&key=berkeley.apis.1180',
    'p.tebt;3.2;888 / 5441' => 'http://wwwapp.cc.columbia.edu/ldpd/app/apis/item?mode=item&key=berkeley.apis.1168',
    'p.tebt;3.2;910 / 5448' => 'http://wwwapp.cc.columbia.edu/ldpd/app/apis/item?mode=item&key=berkeley.apis.1181',
    'p.tebt;3.2;958 / 5473' => 'http://wwwapp.cc.columbia.edu/ldpd/app/apis/item?mode=item&key=berkeley.apis.1276',
    'p.tebt;3.2;934 / 5460' => 'http://wwwapp.cc.columbia.edu/ldpd/app/apis/item?mode=item&key=berkeley.apis.1216',
    'p.tebt;3.1;765 / 5360' => 'http://wwwapp.cc.columbia.edu/ldpd/app/apis/item?mode=item&key=berkeley.apis.988',
    'p.tebt;3.1;782 / 5370' => 'http://wwwapp.cc.columbia.edu/ldpd/app/apis/item?mode=item&key=berkeley.apis.1003',
    'p.tebt;3.2;952 / 5468' => 'http://wwwapp.cc.columbia.edu/ldpd/app/apis/item?mode=item&key=berkeley.apis.1268',
    'p.tebt;3.2;924 / 5455' => 'http://wwwapp.cc.columbia.edu/ldpd/app/apis/item?mode=item&key=berkeley.apis.1203',
    'p.tebt;3.2;956 / 5472' => 'http://wwwapp.cc.columbia.edu/ldpd/app/apis/item?mode=item&key=berkeley.apis.960',
    'p.tebt;3.1;788 / 5374' => 'http://wwwapp.cc.columbia.edu/ldpd/app/apis/item?mode=item&key=berkeley.apis.1009',
    'p.tebt;3.1;733 / 5336' => 'http://wwwapp.cc.columbia.edu/ldpd/app/apis/item?mode=item&key=berkeley.apis.959',
    'p.tebt;3.2;964 / 5476' => 'http://wwwapp.cc.columbia.edu/ldpd/app/apis/item?mode=item&key=berkeley.apis.1282',
    'p.tebt;3.1;789 / 5375' => 'http://wwwapp.cc.columbia.edu/ldpd/app/apis/item?mode=item&key=berkeley.apis.1010',
    'p.tebt;3.2;959 / 5474' => 'http://wwwapp.cc.columbia.edu/ldpd/app/apis/item?mode=item&key=berkeley.apis.1277',
    'p.tebt;1;6 / 3642' => 'http://wwwapp.cc.columbia.edu/ldpd/app/apis/item?mode=item&key=berkeley.apis.448',
    'p.tebt;3.2;912 / 5449' => 'http://wwwapp.cc.columbia.edu/ldpd/app/apis/item?mode=item&key=berkeley.apis.1188',
    'p.tebt;3.1;787 / 5373' => 'http://wwwapp.cc.columbia.edu/ldpd/app/apis/item?mode=item&key=berkeley.apis.1008',
    'p.tebt;3.2;913 / 5450' => 'http://wwwapp.cc.columbia.edu/ldpd/app/apis/item?mode=item&key=berkeley.apis.1190',
    'sb;24;16134 / 79335' => 'http://wwwapp.cc.columbia.edu/ldpd/app/apis/item?mode=item&key=yale.apis.0046090000',
    'p.tebt;3.2;929 / 5458' => 'http://wwwapp.cc.columbia.edu/ldpd/app/apis/item?mode=item&key=berkeley.apis.1207',
    'p.tebt;4;1118 / 3807' => 'http://wwwapp.cc.columbia.edu/ldpd/app/apis/item?mode=item&key=berkeley.apis.887',
    'p.tebt;3.1;768 / 7848' => 'http://wwwapp.cc.columbia.edu/ldpd/app/apis/item?mode=item&key=berkeley.apis.991',
    'p.tebt;3.2;904 / 5446' => 'http://wwwapp.cc.columbia.edu/ldpd/app/apis/item?mode=item&key=berkeley.apis.1175',
    'p.tebt;4;1119 / 3848' => 'http://wwwapp.cc.columbia.edu/ldpd/app/apis/item?mode=item&key=berkeley.apis.1439',
    'p.tebt;4;1128 / 3804' => 'http://wwwapp.cc.columbia.edu/ldpd/app/apis/item?mode=item&key=berkeley.apis.889',
    'chr.wilck;;331 / 3663b' => 'http://wwwapp.cc.columbia.edu/ldpd/app/apis/item?mode=item&key=berkeley.apis.1468',
    'p.tebt;3.1;792 / 5378' => 'http://wwwapp.cc.columbia.edu/ldpd/app/apis/item?mode=item&key=berkeley.apis.1017',
    'p.tebt;4;1114 / 3779' => 'http://wwwapp.cc.columbia.edu/ldpd/app/apis/item?mode=item&key=berkeley.apis.872',
    'p.tebt;3.2;950 / 5467' => 'http://wwwapp.cc.columbia.edu/ldpd/app/apis/item?mode=item&key=berkeley.apis.1267',
    'p.tebt;3.2;878 / 5433' => 'http://wwwapp.cc.columbia.edu/ldpd/app/apis/item?mode=item&key=berkeley.apis.1159',
    'p.tebt;4;1115 / 3780' => 'http://wwwapp.cc.columbia.edu/ldpd/app/apis/item?mode=item&key=berkeley.apis.875',
    'p.tebt;1;105 / 3741' => 'http://wwwapp.cc.columbia.edu/ldpd/app/apis/item?mode=item&key=berkeley.apis.490',
    'p.tebt;1;117 / 3753' => 'http://wwwapp.cc.columbia.edu/ldpd/app/apis/item?mode=item&key=berkeley.apis.867',
    'p.tebt;1;211 / 3844a' => 'http://wwwapp.cc.columbia.edu/ldpd/app/apis/item?mode=item&key=berkeley.apis.1444',
    'p.tebt;1;211 / 3844b' => 'http://wwwapp.cc.columbia.edu/ldpd/app/apis/item?mode=item&key=berkeley.apis.1445',
    'p.tebt;1;202 / 3835' => 'http://wwwapp.cc.columbia.edu/ldpd/app/apis/item?mode=item&key=berkeley.apis.926',
    'p.princ;2;23 / 44956' => 'http://wwwapp.cc.columbia.edu/ldpd/app/apis/item?mode=item&key=princeton.apis.p598',
    'p.mich;5;264/265dupl / 12099' => 'http://wwwapp.cc.columbia.edu/ldpd/app/apis/item?mode=item&key=michigan.apis.3032',
    'p.mich;8;464 / 17238' => 'http://wwwapp.cc.columbia.edu/ldpd/app/apis/item?mode=item&key=michigan.apis.2619',
    'sb;20;14337 / 23719' => 'http://wwwapp.cc.columbia.edu/ldpd/app/apis/item?mode=item&key=uts.apis.5',
    'p.mich;8;466 / 17240' => 'http://wwwapp.cc.columbia.edu/ldpd/app/apis/item?mode=item&key=michigan.apis.2586',
    'p.mich;9;555/556dupl / 12048' => 'http://wwwapp.cc.columbia.edu/ldpd/app/apis/item?mode=item&key=michigan.apis.2548',
    'p.mich;6;427 / 12265' => 'http://wwwapp.cc.columbia.edu/ldpd/app/apis/item?mode=item&key=michigan.apis.1691',
    'p.mich;6;428 / 12266' => 'http://wwwapp.cc.columbia.edu/ldpd/app/apis/item?mode=item&key=michigan.apis.1740',
    'p.mich;6;422 / 12261' => 'http://wwwapp.cc.columbia.edu/ldpd/app/apis/item?mode=item&key=michigan.apis.1698',
    'p.yale;1;30 / 8272 #2' => 'http://wwwapp.cc.columbia.edu/ldpd/app/apis/item?mode=item&key=yale.apis.0000240000',
    'p.yale;1;29 / 8214 #2' => 'http://wwwapp.cc.columbia.edu/ldpd/app/apis/item?mode=item&key=yale.apis.0021920000',
    'p.yale;1;36 / 6204 #2' => 'http://wwwapp.cc.columbia.edu/ldpd/app/apis/item?mode=item&key=yale.apis.0016470031',
    'p.tebt;3.2;912 / 5449 #2' => 'http://wwwapp.cc.columbia.edu/ldpd/app/apis/item?mode=item&key=berkeley.apis.1185',
    'p.mich;6;427 / 12265 #2' => 'http://wwwapp.cc.columbia.edu/ldpd/app/apis/item?mode=item&key=michigan.apis.1691',
    // added manually
    'p.mich;6;427 / 12265 #3'  => 'http://wwwapp.cc.columbia.edu/ldpd/apis/item?mode=item&key=michigan.apis.1691',
    'p.yale;1;30 / 8272 #3'    => 'http://wwwapp.cc.columbia.edu/ldpd/apis/item?mode=item&key=yale.apis.0000240000',
    'p.yale;1;29 / 8214 #3'    => 'http://wwwapp.cc.columbia.edu/ldpd/apis/item?mode=item&key=yale.apis.0021920000',
    'p.tebt;3.2;866 / 5428 #2' => 'http://wwwapp.cc.columbia.edu/ldpd/app/apis/item?mode=item&key=berkeley.apis.11512', // item not found
    'p.tebt;3.2;846 / 5417 #2' => 'http://wwwapp.cc.columbia.edu/ldpd/app/apis/item?mode=item&key=berkeley.apis.1065', // item not found
    'p.tebt;3.2;912 / 5449 #3' => 'http://wwwapp.cc.columbia.edu/ldpd/app/apis/item?mode=item&key=berkeley.apis.1183',
    'sb;14;11498 / 4246' => 'http://wwwapp.cc.columbia.edu/ldpd/app/apis/item?mode=item&key=michigan.apis.1198', // click on image leads to server error
    'p.mich;5;312 / 15163' => 'http://wwwapp.cc.columbia.edu/ldpd/app/apis/item?mode=item&key=michigan.apis.2828', // click on image leads to server error
    'p.kron;;48dupl / 11569' => 'http://wwwapp.cc.columbia.edu/ldpd/app/apis/item?mode=item&key=michigan.apis.1353', // click on image leads to server error
    'p.mich;5;312 / 15163 #2' => 'http://wwwapp.cc.columbia.edu/ldpd/apis/item?mode=item&key=michigan.apis.2828', // click on image leads to server error
    // 2nd run
    'p.princ.roll;;2nded / 14032' => 'http://wwwapp.cc.columbia.edu/ldpd/app/apis/item?mode=item&key=princeton.apis.p201',
    'sb;4;7445 / 18054' => 'http://wwwapp.cc.columbia.edu/ldpd/app/apis/item?mode=item&key=princeton.apis.p404',
    'p.yale;1;71 / 16844' => 'http://wwwapp.cc.columbia.edu/ldpd/app/apis/item?mode=item&key=yale.apis.0003530000',
    'sb;24;15905 / 61399' => 'http://wwwapp.cc.columbia.edu/ldpd/app/apis/item?mode=item&key=yale.apis.0036780000',
    'p.princ;2;82 / 17370' => 'http://wwwapp.cc.columbia.edu/ldpd/app/apis/item?mode=item&key=princeton.apis.p258',
    'p.princ;2;87 / 17371' => 'http://wwwapp.cc.columbia.edu/ldpd/app/apis/item?mode=item&key=princeton.apis.p11',
    'sb;22;15620 / 47269' => 'http://wwwapp.cc.columbia.edu/ldpd/app/apis/item?mode=item&key=michigan.apis.2231', // Server Error (when clicking on image link)
    'sb;22;15619 / 47268' => 'http://wwwapp.cc.columbia.edu/ldpd/app/apis/item?mode=item&key=michigan.apis.2279'
  );

  public static $BROKEN_HGV_IMAGE_LINKS = array(
    'sb;16;12344 / 4088'    => 'http://www.hum.ku.dk/cni/papcoll/pc48.jpg',
    'sb;18;13314 / 2549'    => 'http://www.hum.ku.dk/cni/papcoll/pc51.jpg',
    'p.count;;6 / 107323'   => 'http://www.papyrologie.paris4.sorbonne.fr/menu1/collections/pgrec/pcount.htm#pcount06',
    'pcount2'               => 'http://www.papyrologie.paris4.sorbonne.fr/menu1/collections/pgrec/pcount.htm#pcount03',
    'pcount24'              => 'http://www.papyrologie.paris4.sorbonne.fr/menu1/collections/pgrec/preinach.htm',
    'p.tebt;3.1;704 / 5316' => 'http://papyri-leipzig.dl.uni-leipzig.de/receive/HalPapyri_schrift_00000020',
    'p.count;;47 / 44402'   => 'http://pcclu07.rz.uni-leipzig.de:8491/servlets/MCRQueryServlet?mode=ObjectMetadata&status=2&type=schrift&layout=simple&hosts=local&lang=de&query=/mycoreobject%5B@ID=\'IAwJPapyri_schrift_00008840\'%5D',
    'p.koeln;6;273 / 3203' => 'http://www.uni-koeln.de/phil-fak/ifa/NRWakademie/papyrologie/Karte/VI_273.html', // picture temporarily not available
    'p.koeln;3;157 / 21227' => 'http://www.uni-koeln.de/phil-fak/ifa/NRWakademie/papyrologie/Karte/III_157.html', // Papyrus in Restaurierung
    'p.ryl;4;589 / 65627b' => 'http://enriqueta.man.ac.uk:8081/BrowserInsight/BrowserInsight?cmd=start&un=uman&pw=est1824=&cid=ManchesterDev-93-NA&iia=0&ig=Rylands%20Papyri&isl=0&gwisp=0%7CReference_number%7CReference%20number%7C1%7CGreek%20Papyrus%20589:%20Fragment%204%7C1&gwia=3&gc=0&ir=100286&id=22523&iwas=2',
    'p.ryl;4;586 / 5736a' => 'http://enriqueta.man.ac.uk:8081/BrowserInsight/BrowserInsight?cmd=start&cid=30&ig=1050&ir=100493&id=38660&iwc=1&iwas=2&iwo=true&d=0&iia=1&gwl=1&gws=1&ss=0',
    'upz;1;62 / 3453' => 'http://www.photo.rmn.fr/cf/htm/CSearchZ.aspx?o=&Total=332&FP=43494746&E=22S39UWBEYDWA&SID=22S39UWBEYDWA&New=T&Pic=303&SubE=2C6NU0P3TT66',
    'p.polit.iud;;10 / 44626' => 'http://www.rzuser.uni-heidelberg.de/~gv0/Papyri/P.Polit.Iud./01/P.Polit.Iud._10.html',
    'p.polit.iud;;2 / 44618' => 'http://www.rzuser.uni-heidelberg.de/~gv0/Papyri/P.Polit.Iud./01/P.Polit.Iud._2.html',
    'p.polit.iud;;4' => 'http://www.rzuser.uni-heidelberg.de/~gv0/Papyri/P.Polit.Iud./01/P.Polit.Iud._4.html',
    'p.polit.iud;;11 / 44627' => 'http://www.rzuser.uni-heidelberg.de/~gv0/Papyri/P.Polit.Iud./01/P.Polit.Iud._11.html',
    'p.bon;;14 / 5564' => 'http://www.librit.unibo.it/servlet/ParseHtml/page/frames/index.html?idimmagine=&idoggetto=84',
    'p.bon;;10 / 5028' => 'http://www.librit.unibo.it/servlet/ParseHtml/page/frames/index.html?idimmagine=&idoggetto=76',
    'p.adl;;G13 / 13a' => 'http://www.nb.no/baser/schoyen/4/4.4/45.html#140',
    'p.adl;;G13 / 13b' => 'http://www.nb.no/baser/schoyen/4/4.4/45.html#140',
    'sb;18;13236 / 8685' => 'http://www.ucl.ac.uk/GrandLat/hawara/papydata/phaw_244.htm',
    'p.oxy;38;2836 / 22225' => 'http://163.1.169.40/cgi-bin/library?a=q&r=1&hs=1&e=p-000-00---0POxy--00-0-0--0prompt-10---4------0-1l--1-en-50---20-about---00031-001-1-0utfZz-e=item&key=michigan.apis.3109',
    'p.hal;;8 / 5878' =>'http://papyri-leipzig.dl.uni-leipzig.de/receive/HalPapyri_schrift_00000020',
    'p.gen.2;1;9 / 23600b' => 'http://www.ville-ge.ch/musinfo/collections/bge/papyrus/details.jsp?showpage=0&useimg=FALSE&advSearch=TRUE&advCollection=P.Gen.&advNoInv=50&advNoPublication=9',
    'o.petr;;425 / 75582' => 'http://www.petrie.ucl.ac.uk/detail/details/index_no_login.php?objectid=UC62097',
    'o.petr;;433 / 75590' => 'http://www.petrie.ucl.ac.uk/detail/details/index_no_login.php?objectid=UC62105',
    'o.petr;;464 / 75617' => 'http://www.petrie.ucl.ac.uk/detail/details/index_no_login.php?objectid=UC62133',
    'o.petr;;466 / 75619' => 'http://www.petrie.ucl.ac.uk/detail/details/index_no_login.php?objectid=UC32044',
    'o.petr;;468 / 75620' => 'http://www.petrie.ucl.ac.uk/detail/details/index_no_login.php?objectid=UC32039',
    'sb;16;13035 / 16349' => 'http://www.librit.unibo.it/servlet/ParseHtml/page/frames/index.html?idimmagine=&idoggetto=114'
    
    /*'bgu;7;1656 / 9536' => 'http://www.papyrology.uw.edu.pl/papyri/pberlin11467.htm',
    'bgu;7;1608 / 9513' => 'http://www.papyrology.uw.edu.pl/papyri/pberlin11503.htm',
    'bgu;7;1641 / 18238' => 'http://www.papyrology.uw.edu.pl/papyri/pberlin11504.htm',
    'bgu;7;1564 / 9473' => 'http://www.papyrology.uw.edu.pl/papyri/pberlin11712.htm',
    'bgu;7;1572 / 9480' => 'http://www.papyrology.uw.edu.pl/papyri/pberlin11713.htm',
    'bgu;7;1647 / 9529' => 'http://www.papyrology.uw.edu.pl/papyri/pberlin11715.htm',
    'sb;18;13878 / 14765' => 'http://www.papyrology.uw.edu.pl/papyri/pberlin11717.htm',
    'bgu;6;1263tripl / 4547' => 'http://www.papyrology.uw.edu.pl/papyri/pberlin11796.htm',
    'bgu;4;1127 / 18570' => 'http://www.papyrology.uw.edu.pl/papyri/pberlin13047r.htm',
    'bgu;4;1122 / 18564' => 'http://www.papyrology.uw.edu.pl/papyri/pberlin13047v.htm',
    'bgu;4;1116 / 18557' => 'http://www.papyrology.uw.edu.pl/papyri/pberlin13048.htm',
    'bgu;4;1182 / 18638' => 'http://www.papyrology.uw.edu.pl/papyri/pberlin13061.htm',
    'sb;20;14375 / 23723' => 'http://www.papyrology.uw.edu.pl/papyri/pberlin13062.htm',
    'bgu;8;1817 / 4896' => 'http://www.papyrology.uw.edu.pl/papyri/pberlin13784.htm',
    'bgu;8;1813 / 4892' => 'http://www.papyrology.uw.edu.pl/papyri/pberlin13786.htm',
    'bgu;8;1771 / 4852' => 'http://www.papyrology.uw.edu.pl/papyri/pberlin13847.htm',
    'bgu;1;183 / 8944' => 'http://www.papyrology.uw.edu.pl/papyri/pberlin6867.htm',
    'bgu;8;1751 / 4833a' => 'http://www.papyrology.uw.edu.pl/papyri/pberlin13960.htm',
    'bgu;8;1751 / 4833b' => 'http://www.papyrology.uw.edu.pl/papyri/pberlin13960.htm',
    'bgu;8;1827 / 4906' => 'http://www.papyrology.uw.edu.pl/papyri/pberlin13848.htm',
    'bgu;1;35 / 9073' => 'http://www.papyrology.uw.edu.pl/papyri/pberlin6873.htm',
    'bgu;1;100 / 8875' => 'http://www.papyrology.uw.edu.pl/papyri/pberlin6924.htm',
    'p.vars;;10 / 13665a' => 'http://www.papyrology.uw.edu.pl/papyri/pvars10.htm',
    'p.vars;;10 / 13665b' => 'http://www.papyrology.uw.edu.pl/papyri/pvars10.htm',
    'p.vars;;10 / 13665c' => 'http://www.papyrology.uw.edu.pl/papyri/pvars10.htm',
    'sb;5;8754 / 5711' => 'http://www.papyrology.uw.edu.pl/papyri/pberlin16876.htm',*/

  );

  public function crawl($url, $ddb){
    $this->ddb = $ddb;
    $this->url = $url;

    if($matches = RegExp::search($url, '^(http://(www\.)?)?([^/\?]+)(/[^\?]+)?(\?.*)?$')){
      
      $this->urlProtocol   = $matches[1];
      $this->urlHost       = $matches[3];
      $this->urlPath       = $matches[4];
      $this->urlParameters = $matches[5];

    } else {
      throw new Exception('url cannot be parsed ' . $url);
    }

    foreach(self::$TYPE_LIST as $type => $pattern){
      if(strstr($url, $pattern) !== FALSE){
        $this->type = $type;
        break;
      }
    }

    if($this->type){
      $method = 'getImages' . ucfirst($this->type);
      if(!in_array($url, self::$BROKEN_HGV_IMAGE_LINKS)){
        $this->$method(in_array($this->type, self::$TYPE_MODE_URL) ? $this->url : self::getHtml($this->url));
      } else {
        //Log::message('broken hgv image link (' . $url . ')');
		throw new Exception('broken hgv image link (' . $url . ')');
      }
    } else {
      throw new Exception('no type found for ' . $this->url);
    }
  }

  public function __toString(){
    return $this->urlProtocol . $this->urlHost . $this->urlPath . $this->urlParameters . ' (' . $this->type . ')' . "\n" . (count($this->images) ? (implode("\n", $this->images)) : 'no images');
  }

  public function addImage(Image $image){
    $this->images[] = $image;
  }
  
  // Photographic Archive of Papyri in the Cairo Museum
  // <a onClick="m()" target="detail" href="/Elephantine-bw/72dpi/P.Eleph.3r(i).jpg">72 dpi image (b/w)</a>
  // <a onClick="m()" target="detail" href="/PCZ-colour/72dpi/P.Cair.Zen.I.59080r.jpg">72 dpi image (colour)</a>
  // <a href="/Maspero-colour/72dpi/P.Cair.Masp.I.67103.jpg " target="detail" onclick="m()">72 dpi image (colour)</a>
  protected function getImagesIpap($html){
    if($matches = RegExp::searchAll($html, '<a [^>]*href="(/[^"]*/72dpi[^"]*/([^"/]+\.(jpg|JPG))) *">([^<]*)</a>')){
      foreach($matches as $match){
        $url = $this->generateUrl($match[1]);
        $name =  $match[2];
        $description = $match[3];
        $this->addImage(new Image($url, $name, $description));
      }
    } else if($matches = RegExp::searchAll($html, 'Higher resolution images of this papyrus are available on <a [^>]*href="mailto:csad@classics.ox.ac.uk"><u>application</u></a>')){
      //Log::message('application for higher resolution required');
	  throw new Exception('application for higher resolution required');
    } else {
      throw new Exception('no images could be parsed from url ' . $this->url . ' for type ' .  $this->type);
    }
  }
  
  // Institut français d’archéologie orientale - Le Caire
  // <a href='docs/zooms/047.jpg' title='zoom photo'><img src='docs/vignettes/047.jpg'/></a>
  // href='docs/zooms/047.jpg'
  protected function getImagesIfao($html){
    if($matches = RegExp::searchAll($html, '<a [^>]*href=["\'](docs/zooms/(\d+\.(jpg|JPG)))["\'][^>]*>')){
      foreach($matches as $match){
        $url = $this->generateUrl($match[1]);
        $name =  $match[2];
        $description = "Institut français d’archéologie orientale - Le Caire";
        $this->addImage(new Image($url, $name, $description));
      }
    } else {
      throw new Exception('no images could be parsed from url ' . $this->url . ' for type ' .  $this->type);
    }
  }

  // PSIonline - Papiri della societa italiana
  // http://www.psi-online.it/documents/download?filen=PSI%20I%20111.jpg
  // http://www.psi-online.it/images/orig/PSI%20I%20111.jpg?1184955400
  // http://www.psi-online.it/images/orig/PSI I 111.jpg?1184955400
  // http://www.psi-online.it/images/orig/PSI%20I%20111.jpg
  // <a title="PSI I 111.jpg" name="/documents/download?filen=PSI I 111.jpg" class="group1 cboxElement" href="/images/orig/PSI I 111.jpg?1184955400"><img border="0" src="/images/thumbs/PSI I 111.jpg?1333294386" alt="PSI I 111.jpg"></a>
  protected function getImagesPsio($html){
    file_put_contents('TT', $html);
    $index = 0;
    if($matches = RegExp::searchAll($html, '<a [^>]*href="(/images/orig/(PSI [IVXLCDM]+ \d+[^\.]*\.jpg))\?\d+"')){
      foreach($matches as $match){
        $url = $this->generateUrl(str_replace(' ', '%20', $match[1]));
        $name =   $match[2];
        $description = 'PSIonline - Papiri della societa italiana';
        $this->addImage(new Image($url, $name, $description));
      }
    } else {
      throw new Exception('no images could be parsed from url ' . $this->url . ' for type ' .  $this->type);
    }
  }
  
  // APIS – Advanced Papyrological Information System
  // <a href="http://brbl-svr1.library.yale.edu/papyrimg/S4512875.JPG">[2194 (A)] : Screen</a>
  // <a href="http://images.umdl.umich.edu/cgi/i/image/getimage-idx?cc=apis&entryid=X-1825&viewid=3135R_A.TIF&quality=medium">Overview : medium</a>
  // <a href="http://images.umdl.umich.edu/cgi/i/image/getimage-idx?cc=apis&amp;entryid=X-1722&amp;viewid=2977.TIF&amp;quality=medium">Overview : medium</a>
  // <a href="http://www.columbia.edu/cgi-bin/dlo?obj=columbia.apis.p3&size=300&face=b&tile=0">large-sized image of back tile: 0</a>
  // <a href="http://www.columbia.edu/cgi-bin/dlo?obj=columbia.apis.p11&size=300&face=f&tile=0">
  // <a href="http://www.columbia.edu/cgi-bin/dlo?obj=toronto.apis.12&size=150&face=f&tile=0">medium-sized image of front tile: 0</a>
  // <a href="http://scriptorium.lib.duke.edu/papyrus/images/150dpi/677-at150.gif">Recto : 150dpi</a>
  // <a href="http://sunsite.berkeley.edu/cgi-bin/apisdb/image/medhires/AP04034a">Verso : 150 dpi</a>
  protected function getImagesApis($html){
    $index = 0;
    if($matches = RegExp::searchAll($html, '<a [^>]*href="(http://brbl-svr1.library.yale.edu/papyrimg[^"]*/([^"/]+\.(jpg|JPG)))">([^<]*)</a>')){
      foreach($matches as $match){
        $url = $match[1];
        $name =   $match[2];
        $description = $match[3];
        $this->addImage(new Image($url, $name, $description));
      }
    } else if($matches = RegExp::searchAll($html, '<a [^>]*href="(http://scriptorium.lib.duke.edu/papyrus/images[^"]*/([^"]+))"[^>]*>([^<]*)</a>')) {
      foreach($matches as $match){
        if((stristr($match[3], 'thumbnail') === FALSE) && (stristr($match[3], 'verso') === FALSE)){
          $url = $match[1];
          $name =  $match[2];
          $description = $match[3] . ' (duke)';
          $this->addImage(new Image($url, $name, $description));
        }
      }
    } else if($matches = RegExp::searchAll($html, '<a [^>]*href="(http://opes\.uio\.no/papyrus/scan/(\d+r\.jpg))">([^<]*medium[^>]*)</a>')) {
      foreach($matches as $match){
        if((stristr($match[3], 'thumbnail') === FALSE) && (stristr($match[3], 'verso') === FALSE)){
          $url = $match[1];
          $name =  $match[2];
          $description = $match[3] . ' (opes.uio.no)';
          $this->addImage(new Image($url, $name, $description));
        }
      }
    } else if($matches = RegExp::searchAll($html, '<a [^>]*href="(http://images\.umdl\.umich\.edu/cgi/i/image/getimage-idx\?cc=apis&(amp;)?entryid=([^&]+)&(amp;)?viewid=[^&]+R[^&]+&(amp;)?[^"]+medium)">([^<]*)</a>')) {
      foreach($matches as $match){
        if((stristr($match[3], 'thumbnail') === FALSE) && (stristr($match[3], 'verso') === FALSE)){
          $url = $match[1];
          $name =  $match[3] . '_' . $index++ . '.tiff';
          $description = $match[5];
          $this->addImage(new Image($url, $name, $description));
        }
      }
    } else if($matches = RegExp::searchAll($html, '<a [^>]*href="(http://images\.umdl\.umich\.edu/cgi/i/image/getimage-idx\?cc=apis&(amp;)?entryid=([^&]+)&(amp;)?[^"]+medium)">([^<]*)</a>')) {
      foreach($matches as $match){ // the same as above but without insisting on [R]ecto
        if((stristr($match[3], 'thumbnail') === FALSE) && (stristr($match[3], 'verso') === FALSE)){
          $url = $match[1];
          $name =  $match[3] . '_' . $index++ . '.tiff';
          $description = $match[5];
          $this->addImage(new Image($url, $name, $description));
        }
      }
    } else if ($matches = RegExp::searchAll($html, '<a [^>]*href="(http://www.columbia.edu/cgi-bin/dlo\?obj=(columbia|princeton|toronto|nyu|\w+).apis.p?(\d+)&size=(\d+)&face=(f)(&tile=([^&="]+))?)">([^<]*)</a>')) {
      foreach($matches as $match){
        if((stristr($match[3], 'thumbnail') === FALSE) && (stristr($match[3], 'verso') === FALSE)){
          $url = $match[1];
          $name =  'p'. $match[3] . 'size'. $match[4] . '_face' . $match[5] . '_tile' . $match[7] . '.jpg';
          $description = $match[8];
          $this->addImage(new Image($url, $name, $description));
        }
      }
    } else if ($matches = RegExp::searchAll($html, '<a [^>]*href="(http://www.columbia.edu/cgi-bin/dlo\?obj=(columbia|princeton|toronto|nyu|\w+).apis.p?(\d+)&size=(\d+)&face=(b)(&tile=([^&="]+))?)">([^<]*)</a>')) {
      foreach($matches as $match){ // use reverse side if there is no recto image
        if(stristr($match[3], 'thumbnail') === FALSE){
          $url = $match[1];
          $name =  'p'. $match[3] . 'size'. $match[4] . '_back' . $match[5] . '_tile' . $match[7] . '.jpg';
          $description = $match[8];
          $this->addImage(new Image($url, $name, $description));
        }
      }
    } else if ($matches = RegExp::searchAll($html, '<a [^>]*href="(http://sunsite.berkeley.edu/[^"]+/([^/"]+))"[^>]*>([^<]*Recto[^<]*150[^<]*)</a>')) {
      // <a href="http://sunsite.berkeley.edu/cgi-bin/apisdb/image/medhires/AP02534a">Frame 1/4 Recto : 150 dpi</a>
      foreach($matches as $match){
        if((stristr($match[3], 'thumbnail') === FALSE) && (stristr($match[3], 'verso') === FALSE)){
          $url = $match[1];
          $name =  'recto_150_' . $match[2] . '.jpg';
          $description = $match[3];
          $this->addImage(new Image($url, $name, $description));
        }
      }
    } else if ($matches = RegExp::searchAll($html, '<a [^>]*href="(http://sunsite.berkeley.edu/[^"]+/([^/"]+))"[^>]*>([^<]*150[^<]*)</a>')) {
      // same as above but without »Recto« constraint
      foreach($matches as $match){
        if(stristr($match[3], 'thumbnail') === FALSE){
          $url = $match[1];
          $name =  '150_' . $match[2] . '.jpg';
          $description = $match[3];
          $this->addImage(new Image($url, $name, $description));
        }
      }
    } else if ($key = array_search($this->url, self::$APIS_MISSING_IMAGE_LIST)) {
      //Log::Message('ImageCrawler::getImagesApis special case> '. $key . ' has no apis images (' . $this->url . ')');
	  throw new Exception('ImageCrawler::getImagesApis special case> '. $key . ' has no apis images (' . $this->url . ')');
    } else {
      throw new Exception('no images could be parsed from url ' . $this->url . ' for type ' .  $this->type);
    }
  }

  // Giessener Papyri- und Ostrakadatenbank
  // <a href="http://bibd.uni-giessen.de/papyri/images/piand-inv404recto.jpg">recto</a>
  // <a href="http://bibd.uni-giessen.de/ostr/images/ostrgiss-inv022.jpg">Normal</a>
  protected function getImagesDigibib($html){
    if($matches = RegExp::searchAll($html, '<a [^>]*href="(http://bibd.uni-giessen.de/(papyri|ostr)/images/([^"]+\.(jpg|JPG)))">([^<]*)</a>')){
      foreach($matches as $match){
        $url = $match[1];
        $name =  $match[3];
        $description = $match[4];
        $this->addImage(new Image($url, $name, $description));
      }
    } else {
      throw new Exception('no images could be parsed from url ' . $this->url . ' for type ' .  $this->type);
    }
  }

  // Papyri und Ostraka Projekt
  // http://papyri-leipzig.dl.uni-leipzig.de/receive/UBLPapyri_schrift_00002320
  // <a target="_blank" href="http://papyri-leipzig.dl.uni-leipzig.de/servlets/MCRIViewServlet/UBLPapyri_derivate_00001210/PLipsInv605R300.jpg?mode=generateLayout&amp;XSL.MCR.Module-iview.navi.zoom.SESSION=fitToScreen&amp;XSL.MCR.Module-iview.display.SESSION=normal&amp;XSL.MCR.Module-iview.style.SESSION=image&amp;XSL.MCR.Module-iview.lastEmbeddedURL.SESSION=http%3A%2F%2Fpapyri-leipzig.dl.uni-leipzig.de%2Freceive%2FUBLPapyri_schrift_00001210&amp;XSL.MCR.Module-iview.embedded.SESSION=false&amp;XSL.MCR.Module-iview.move=reset"> 300 dpi </a>
  protected function getImagesPop($html){
    foreach(array('300', '100', '72') as $dpi){
      if($match = RegExp::search($html, 'href="(http://papyri-(leipzig|wuerzburg).dl.uni-leipzig.de/servlets/MCRIViewServlet/\w+Papyri_derivate_[^"]+/([^"]+' . $dpi . '\.jpg))[^"]*"')){
        $url = str_replace('MCRIViewServlet', 'MCRFileNodeServlet', $match[1]);
        $name =  $match[3];
        $description = 'Papyri und Ostraka Projekt';
        $this->addImage(new Image($url, $name, $description));
        break;
      }
    }

    if(!count($this->images)){
      if(!in_array($this->url, self::$POP_MISSING_IMAGE_LIST)){
        throw new Exception('no images could be parsed from url ' . $this->url . ' for type ' .  $this->type);
      }
    }
  }
  
  // Global Egyptian Museum
  // http://www.globalegyptianmuseum.org/record.aspx?id=925
  // <a title="BrE.5956(1).jpg" href="javascript:large('images%2fKMKG-MRAH%2fBrE.5956(1).jpg')"><img align="left" src="images/KMKG-MRAH/_100/bre.5956(1).jpg" alt="BrE.5956(1).jpg"/></a>
  protected function getImagesGem($html){
    if($matches = RegExp::searchAll($html, '<a [^>]*href="javascript:large\(\'([^\']+)\'\)"[^>]*><img [^>]*alt="([^"]+)"[^>]*/></a>')){
      foreach($matches as $match){
        $url = 'http://www.globalegyptianmuseum.org/' . $match[1];
        $name =  $match[2];
        $description = 'Global Egyptian Museum';
        $this->addImage(new Image($url, $name, $description));
      }
    } else {
      throw new Exception('no images could be parsed from url ' . $this->url . ' for type ' .  $this->type);
    }
  }
  
  // Handschriftenkatalog der UB Graz
  // http://www-classic.uni-graz.at/ubwww/sosa/katalog/katalogisate/1924.html
  // <A HREF="../images/1924/1924r.jpg"><IMG SRC="../images/1924/TN_1924r.JPG"><BR></A>
  // <a href="../images/2117/Papyrus2117.jpg"><img src="../images/2117/TN_Papyrus2117.JPG" alt="TN_Papyrus2117.JPG"><br></a>
  protected function getImagesGraz($html){
    if($matches = RegExp::searchAll($html, '<A [^>]*HREF="(../images/[^"]+/([^/"]+r?.jpg))"><IMG [^>]*SRC="[^"]+"[^>]*>(<BR>)?</A>')){
      foreach($matches as $match){
        $url = $this->generateUrl($match[1]);
        $name =  $match[2];
        $description = 'Handschriftenkatalog der UB Graz';
        $this->addImage(new Image($url, $name, $description));
      }
    } else if ($key = array_search($this->url, self::$GRAZ_MISSING_IMAGE_LIST)) {
      //Log::Message('ImageCrawler::getImagesGraz special case> '. $key . ' has no graz images OR site not found (' . $this->url . ')');
      throw new Exception('ImageCrawler::getImagesGraz special case> '. $key . ' has no graz images OR site not found (' . $this->url . ')');
    } else {
      throw new Exception('no images could be parsed from url ' . $this->url . ' for type ' .  $this->type);
    }
  }

  // Schoenberg Center for Electronic Text & Image
  // http://sceti.library.upenn.edu/pages/index.cfm?so_id=4291
  // http://images.library.upenn.edu/mrsidsceti/bin/image_jpeg2.pl?coll=manuscripts;subcoll=e2825;image=e2825_wk1_body0001.sid
  protected function getImagesSceti($html){
    if($match = RegExp::search($html, '(http://images\.library\.upenn\.edu/mrsidsceti/[^"]+image=([^"]+)\.sid)')){
      $url = $match[1];
      $name =  $match[2] . '.jpg';
      $description = 'Schoenberg Center for Electronic Text & Image';
      $this->addImage(new Image($url, $name, $description));
    } else {
      throw new Exception('no images could be parsed from url ' . $this->url . ' for type ' .  $this->type);
    }
  }
  
  // Katalog der Papyrussammlung der Österreichischen Nationalbibliothek (#54)
  // http://aleph.onb.ac.at/F/?func=find-c&ccl_term=WID%3DRZ00002331&local_base=ONB08
  // javascript/jsp application with frames and several hops to get the picture
  protected function getImagesOnb($html){
    if(in_array($this->url, self::$ONB_MISSING_IMAGE_LIST)){
      //Log::Message('ImageCrawler::getImagesOnb> special case: no image available (' . $this->url . ')');
      throw new Exception('ImageCrawler::getImagesOnb> special case: no image available (' . $this->url . ')');
    } else {
      //Log::Message('ImageCrawler::getImagesOnb> not implemented yet');
	  throw new Exception('ImageCrawler::getImagesOnb> not implemented yet');
    }
  }

  // Hollis Classic (#10)
  // http://hollisclassic.harvard.edu/F?func=find-c&CCL_TERM=sys=009964051
  // internet link with frames
  // <a href="/goto/http://lms01.harvard.edu:80/F/Y8HDFHYQA5CUYV1VDAMSDHNDHPUNN5T11SD7CACLPX1LUI8DPU-01128?func=find-c&CCL_TERM=sys%3D009964051&pds_handle=GUEST">
  // INTERNET LINK :</td><td class="td1c" align="left"><a href="http://nrs.harvard.edu/urn-3:FHCL.HOUGH:1180793" target="new">http://nrs.harvard.edu/urn-3:FHCL.HOUGH:1180793</a>&nbsp;[&nbsp;Digital facsimile image of MS Gr SM4377&nbsp;]
  protected function getImagesHollis($html){

    if($match = RegExp::search($html, 'var url *= *[\'"]([^\'"]+)[\'"];[^"]*var callback_url *= *[\'"]([^\'"]+)[\'"];')){
      $url = $match[1] . html_entity_decode($match[2]);
      $html = self::getHtml($url);

      if($match = RegExp::search($html, '<a href="/goto/(http://[^"]+)">')){
        $url = $match[1];
        $html = self::getHtml($url);
        
        if($match = RegExp::search($html, 'INTERNET LINK.+href="(http://nrs\.harvard\.edu/[^"]+)".+\[[^\]]+image[^\]]+\]')){
          $url = $match[1];
          $html = self::getHtml($url);

          if($match = RegExp::search($html, 'href="/pds/view/(\d+)\?')){
            $url = 'http://pds.lib.harvard.edu/pds/view/' . $match[1] . '?op=t';
            $html = self::getHtml($url);
            
            // http://ids.lib.harvard.edu/ids/view/7456402
            // http://pds.lib.harvard.edu/pds/view/7456402
            // http://ids.lib.harvard.edu/ids/view/7456524?s=.25&rotation=0&width=1200&height=1200&x=-1&y=-1&xcap=mx%2BH1zMK5j7hx82zCIFrFnVueAoTe4xt4BAJZkh2JsTPjKZxGLRfZHUVWuIOLmtOSBKLWZJpy%2BRhr8GLQUKuJhIll%2BCRMIkDTQ9Jz6o%2Fqy54Le1RDlU9P5R2X511h%2BUdGZw9YBLDQ0O467914mfQusCv%2BvjSWUlm1WW6jYOsmXXxhHiEdCjAVvO4NWP0c0F5863xHczKm4kcx%2B91OMLdsVNe7E7lATjPYG4%2B9L4Guj%2BoTFS9ONiLnNHAUqvrIzY%2B
            // http://ids.lib.harvard.edu/ids/view/7456524?s=.25&rotation=0&width=1200&height=1200&x=0&y=0&xcap=mx%2BH1zMK5j7hx82zCIFrFnVueAoTe4xt4BAJZkh2JsTPjKZxGLRfZHUVWuIOLmtOSBKLWZJpy%2BRhr8GLQUKuJhIll%2BCRMIkDTQ9Jz6o%2Fqy54Le1RDlU9P5R2X511h%2BUdGZw9YBLDQ0O467914mfQusCv%2BvjSWUlm1WW6jYOsmXXxhHiEdCjAVvO4NWP0c0F5863xHczKm4kcx%2B91OMLdsVNe7E7lATjPYG4%2B9L4Guj%2BoTFS9ONiLnNHAUqvrIzY%2B
            //                           /pds/view/7456402?op=t&amp;n=1&amp;s=2&amp;rotation=0&amp;imagesize=1200&amp;jp2Res=.25&amp;jp2x=-1&amp;jp2y=-1&amp;bbx1=0&amp;bby1=0&amp;bbx2=107&amp;bby2=130&amp;printThumbnails=no
            // <img src="http://ids.lib.harvard.edu/ids/view/7456524?s=.25&amp;rotation=0&amp;width=1200&amp;height=1200&amp;x=0&amp;y=0&amp;xcap=mx%2BH1zMK5j7hx82zCIFrFnVueAoTe4xt4BAJZkh2JsTPjKZxGLRfZHUVWuIOLmtOSBKLWZJpy%2BRhr8GLQUKuJhIll%2BCRMIkDTQ9Jz6o%2Fqy54Le1RDlU9P5R2X511h%2BUdGZw9YBLDQ0O467914mfQusCv%2BvjSWUlm1WW6jYOsmXXxhHiEdCjAVvO4NWP0c0F5863xHczKm4kcx%2B91OMLdsVNe7E7lATjPYG4%2B9L4Guj%2BoTFS9ONiLnNHAUqvrIzY%2B" title="Sequence 1 of 2" alt="Sequence 1 of 2"/>

            if($match = RegExp::search($html, '<img src="(http://ids.lib.harvard.edu/ids/view/(\d+))\?')){
              $url = $match[1];
              $name =  $match[2] . '.jpg';
              $description = ' FINAL STEP';
              $this->addImage(new Image($url, $name, $description));
            } else {
               throw new Exception('no images could be parsed from url ' . $this->url . ' for type ' .  $this->type . ' (STEP 5)');
            }
          } else {
            throw new Exception('no images could be parsed from url ' . $this->url . ' for type ' .  $this->type . ' (STEP 4)');
          }
        } else {
          throw new Exception('no images could be parsed from url ' . $this->url . ' for type ' .  $this->type . ' (STEP 3)');
        }
      } else {
        throw new Exception('no images could be parsed from url ' . $this->url . ' for type ' .  $this->type . ' (STEP 2)');
      }
    } else {
      throw new Exception('no images could be parsed from url ' . $this->url . ' for type ' .  $this->type . ' (STEP 1)');
    }
  }
  
  // Kelvin Smith Library (#2)
  // http://library.case.edu/digitalcase/imsvr.ashx/vga/ksl/elhrec00/elhrec00.jp2
  // website cannot be called up via Browser
  protected function getImagesLib($url){
    if($match = RegExp::search($url, '^(http://library.case.edu/digitalcase/imsvr.ashx.+/([^/]+\.jp2))$')){
      $url = $url;
      $name =  $match[2];
      $description = 'Kelvin Smith Library';
      $this->addImage(new Image($url, $name, $description));
    } else {
      throw new Exception('no images could be loaded from url ' . $this->url . ' for type ' .  $this->type);
    }
  }

  // Det Humanistiske Fakultet (#2)
  // http://www.hum.ku.dk/cni/papcoll/pc51.jpg
  // broken hgv links
  protected function getImagesHum($html){
    //Log::Message('ImageCrawler::getImagesHum> cannot be implemented (broken hgv links)');
	throw new Exception('ImageCrawler::getImagesHum> cannot be implemented (broken hgv links)');
  }

  // L'Institut de Papyrologie de la Sorbonne, Université de Paris IV (#27)
  // http://www.hum.ku.dk/cni/papcoll/pc51.jpg
  // there are only 3 distinct links, none of them works
  protected function getImagesSorbonne($html){
    //Log::Message('ImageCrawler::getImagesSorbonne> cannot be implemented (there are only 3 distinct links, none of them works)');
	throw new Exception('ImageCrawler::getImagesSorbonne> cannot be implemented (there are only 3 distinct links, none of them works)');
  }

  // Leipzig (#1)
  // http://pcclu07.rz.uni-leipzig.de:8491/servlets/MCRQueryServlet?mode=ObjectMetadata&status=2&type=schrift&layout=simple&hosts=local&lang=de&query=/mycoreobject[@ID=%27IAwJPapyri_schrift_00008840%27]
  // site cannot be called up, image link seems to be broken
  protected function getImagesLeip($html){
    //Log::Message('ImageCrawler::getImagesLeip> cannot be implemented (broken hgv links)');
	throw new Exception('ImageCrawler::getImagesLeip> cannot be implemented (broken hgv links)');
  }

  // Department of Papyrology, University of Warsaw (#26)
  // http://www.papyrology.uw.edu.pl/papyri/pberlin11796.htm
  // problem: Przepraszamy, brak wpisów spełniających podane kryteria. (Sorry, no posts matched your criteria.)
  protected function getImagesWarsaw($html){
     if($matches = RegExp::searchAll($html, '<a [^>]*href="(([^"]+).jpg)"[^>]*>Click here for high resolution</a>')){
      foreach($matches as $match){
        $url = $this->generateUrl($match[1]);
        $name =  $match[1];
        $description = 'Department of Papyrology, University of Warsaw (' . $match[2] . ')';
        $this->addImage(new Image($url, $name, $description));
      }
    } else {
      throw new Exception('no images could be loaded from url ' . $this->url . ' for type ' .  $this->type);
    }
  }

  // Enriqueta (#2)
  // http://enriqueta.man.ac.uk:8081/BrowserInsight/BrowserInsight?cmd=start&un=uman&pw=est1824=&cid=ManchesterDev-93-NA&iia=0&ig=Rylands%20Papyri&isl=0&gwisp=0%7CReference_number%7CReference%20number%7C1%7CGreek%20Papyrus%20589:%20Fragment%204%7C1&gwia=3&gc=0&ir=100286&id=22523&iwas=2
  // problem: Przepraszamy, brak wpisów spełniających podane kryteria. (Sorry, no posts matched your criteria.)
  protected function getImagesEnri($html){
    //Log::Message('ImageCrawler::getImagesEnri> cannot be implemented (and never gonna be? obsolete links: Przepraszamy, brak wpisów spełniających podane kryteria.)');
	throw new Exception('ImageCrawler::getImagesEnri> cannot be implemented (and never gonna be? obsolete links: Przepraszamy, brak wpisów spełniających podane kryteria.)');
  }

  // APIS BERKELEY DATABASE (#4)
  // http://dpg.lib.berkeley.edu/webdb/apis/apis2?sort=Author_Title&invno=1088
  // <a href="http://dpg.lib.berkeley.edu/webdb/apis/apis2?invno=1088&amp;sort=Author_Title&amp;item=1"><font face="Arial" size="-1">View detail</font></a>
  // <a href="javascript: void(0)" onclick="openNewWindow('http://digitalassets.lib.berkeley.edu/apis/ucb/images/AP03345aB.jpg')" style="text-decoration: none;" name="newwin">Med res</a>
  protected function getImagesDpg($html){
    if($details = RegExp::searchAll($html, '<a [^>]*href="(http://dpg.lib.berkeley.edu/webdb/apis/[^"]*)"[^>]*>[^<]*<font [^>]*>[^<]*View detail[^<]*</font>[^<]*</a>')){
      foreach($details as $detail){
        $url = $detail[1];
        $html = self::getHtml($url);
        foreach(array('Med', 'High', 'Low') as $resolution){
          if($matches = RegExp::searchAll($html, '<a [^>]+(http://digitalassets.lib.berkeley.edu/apis[^"]*/([^/]*\.jpg))[^>]+>[^<]*' . $resolution . '[^<]*</a>')){
            foreach($matches as $match){
              $url = $match[1];
              $name =  $match[2];
              $description = 'FINAL STEP';
              $this->addImage(new Image($url, $name, $description));
            }
            break;
          }
        }
      }
    } else {
      throw new Exception('no images could be loaded from url ' . $this->url . ' for type ' .  $this->type . ' (STEP 1)');
    }
  }

  // EyeSpy Launch Pad (#38)
  // http://www.ville-ge.ch/fcgi-bin/fcgi-axn?launchpad&/home/minfo/bge/papyrus/pgen404-1ri.axs&550&550
  // difficult
  protected function getImagesVille($url){
    $url = str_replace('launchpad', 'getbrwx', $url) . '&100&100&2&bgcolor=%23FFFFFF&alig=0&contenttype=image/jpeg&100&100';
    //                                                   |_________> horizontal/vertical offset of clipping
    //                                                           |______> reduction factor (may be 1, 2, 4 or 8)
    //                                                                                                              |_________> horizontal/vertical position of clipping

    // get image information
    $images = array();
    $html = self::getHtml($url);
    if($nobrList = RegExp::searchAll($html, '<nobr>.+?</nobr>')){
      $row = 0;
      foreach($nobrList as $nobr){
        $nobr = $nobr[0];
        if((strstr($nobr, 'Click to view desired area') === FALSE) && (strstr($nobr, 'Click to zoom in') !== FALSE)){
          if($inputList = RegExp::searchAll($nobr, '<(input|img) [^>]*src="([^"]+)" [^>]*width="([^"]+)" [^>]*height="([^"]+)"[^>]*>')){
            $col = 0;
            foreach($inputList as $input){
              $image = array('url' => 'http://www.ville-ge.ch' . $input[2], 'width' => $input[3], 'height' => $input[4]);
              $images[$row][$col++] = $image;
            }
          } else {
            throw new Exception('no images could be loaded from url ' . $this->url . ' for type ' .  $this->type . ' (STEP 2)');
          }
          $row++;
        }
      }
    } else {
      throw new Exception('no images could be loaded from url ' . $this->url . ' for type ' .  $this->type . ' (STEP 1)');
    }
    
    // discard last column of each row and abandon last row (which contain irregular image tiles)
    array_pop($images);
    foreach($images as $rowNumber => $row){
      array_pop($images[$rowNumber]);
    }

    // calculate total dimensions of result image
    $width = $height = 0;
    foreach($images as $row){
      if(count($row)){
        $firstImage = $row[0];
        $height += $firstImage['height'];
      }
    }
    if(count($images)){
      foreach($images[0] as $image){
        $width += $image['width'];
      }
    }

    // create image from image information
    /*var_dump($images);
    var_dump($url);
    var_dump($width);
    var_dump($height);*/
    
    $destination = imagecreatetruecolor($width, $height);
    $destinationX = $destinationY = 0;
    foreach($images as $row){
      $destinationX = 0;
      $y = 0;
      foreach($row as $image){
        $source = imagecreatefromjpeg($image['url']);
        imagecopy($destination, $source, $destinationX, $destinationY, 0, 0, $image['width'], $image['height']);
        $destinationX += $image['width'];
        $y = $image['height'];
      }
      $destinationY += $y;
    }
    
    // save image
    $name =  str_replace(array('.', ';', ','), '', $this->ddb) . '.jpg';
    $path = TMP_PATH . '/VilleGeneve/' . $name;
    imagejpeg($destination, $path, 100);
    
    $this->addImage(new Image($path, $name, 'Ville Geneve'));
  }

  // Librit (#2)
  // http://www.librit.unibo.it/servlet/ParseHtml/page/frames/index.html?idimmagine=&idoggetto=84
  // connection time out
  protected function getImagesLibrit($html){
    //Log::Message('ImageCrawler::getImagesLibrit> broken links, connection timeout');
	throw new Exception('ImageCrawler::getImagesLibrit> broken links, connection timeout');
  }

  // Nasjonalbiblioteket (#2)
  // http://www.nb.no/baser/schoyen/4/4.4/45.html#140
  // connection time out
  protected function getImagesNbno($html){
    //Log::Message('ImageCrawler::getImagesNbno> broken links');
	throw new Exception('ImageCrawler::getImagesNbno> broken links');
  }

  // University College London (#1)
  // http://www.ucl.ac.uk/GrandLat/hawara/papydata/phaw_244.htm
  // link is broken
  protected function getImagesUcl($html){
    //Log::Message('ImageCrawler::getImagesUcl> broken links');
	throw new Exception('ImageCrawler::getImagesUcl> broken links');
  }

  // Biblioteca Medicea Laurenziana (#4)
  // http://www.bml.firenze.sbn.it/laformadelibro/sezioni_ing/scheda12.htm
  // <a href="../img_schede_big/12-PSI-428.jpg" target="_blank"><img src="../img/12-PSI-428.jpg" alt="INGRANDISCI IMMAGINE" width="400" height="188"></a>
  protected function getImagesLaurenz($html){
    if($matches = RegExp::searchAll($html, '<a [^>]*href="(../[^"]*/([^"/]*))"[^>]* target="_blank"[^>]*><img [^>]*alt="([^"]*)"[^>]*></a>')){
      foreach($matches as $match){
        $url = $this->generateUrl($match[1]);
        $name =  $match[2];
        $description = $match[3];
        $this->addImage(new Image($url, $name, $description));
      }
    } else {
      throw new Exception('no images could be parsed from url ' . $this->url . ' for type ' .  $this->type);
    }
  }

  // Kölner Papyrus-Sammlung (#80)
  // http://www.uni-koeln.de/phil-fak/ifa/NRWakademie/papyrologie/PPD/PPD_02.html
  // http://www.uni-koeln.de/phil-fak/ifa/NRWakademie/papyrologie/Karte/X_411.html
  // http://www.uni-koeln.de/phil-fak/ifa/NRWakademie/papyrologie/Karte/VII_314.html
  protected function getImagesKoeln($html){
    $maxHops = 4;
    while(($match = RegExp::search($html, '<li>[^<]*Neuedition [^<]*<a [^>]*href="([^"]+\.html)"[^>]*>[^<]*</a>')) && ($maxHops-- > 0)){
      // <li>Neuedition mit neuem Fragment (P. Köln inv. 4722r): <a href="VI_249.html">P.Köln VI 249</a></li>
      $url = $this->generateUrl($match[1]);
      $html = self::getHtml($url);
    }

    if($matches = RegExp::searchAll($html, '<a [^>]*href="((../PKoeln|bilder|/phil-fak/ifa/NRWakademie)[^"]*/([^"/]+\.jpg))"[^>]*>([^>]+(<sup>([^<]+)</sup>[^>]*)?)</a>(<sup>([^<]+)</sup>)?')){
      // <a href="../PKoeln/PK20271-68r.jpg">20271-68</a>
      // <a href="bilder/PK20980r.jpg">
      // <a href="/phil-fak/ifa/NRWakademie/papyrologie/PKoeln/PK20764r.jpg">20764</a>
      foreach($matches as $match){
        if($match[6] != 'v'){
          $url = $this->generateUrl($match[1]);
          $name =  $match[3];
          $description = $match[4]  . ($match[7] ? ' - ' . $match[7] : '');
          $this->addImage(new Image($url, $name, $description));
        }
      }
    } else {
      throw new Exception('no images could be parsed from url ' . $this->url . ' for type ' .  $this->type . ' (count followed links: ' . min(4 - $maxHops, 4) . ')');
    }
  }

  // Cairo Photographic Archive (#1)
  // http://www.csad.ox.ac.uk/Cairo/PCZ59148.html
  // <a onClick="m()" target="portal" href="http://www.csad.ox.ac.uk/Cairo/150dpi/PCZ.59148.r.jpg">Medium (colour slide)</a>
  protected function getImagesCsad($html){
    if(!in_array($this->url, self::$CSAD_APPLICATION_NEEDED)){
      if($matches = RegExp::searchAll($html, '<a [^>]*href="(http://www.csad.ox.ac.uk/Cairo/150dpi/([^/"]+.r.jpg))"[^>]*>([^<]+)</a>')){
        foreach($matches as $match){
          $url = $match[1];
          $name =  $match[2];
          $description = $match[3];
          $this->addImage(new Image($url, $name, $description));
        }
      } else {
        throw new Exception('no images could be parsed from url ' . $this->url . ' for type ' .  $this->type);
      }
    } else {
      //Log::message('application needed for ' . $this->url . ' (type ' . $this->type . ')');
	  throw new Exception('application needed for ' . $this->url . ' (type ' . $this->type . ')');
    }
  }

  // Agence photographique (#2)
  // http://www.photo.rmn.fr/cf/htm/CSearchZ.aspx?o=&Total=251&FP=43482635&E=22S39UWBEVE@6&SID=22S39UWBEVE@6&New=T&Pic=183&SubE=2C6NU00IDDXX
  // <img name="picture" src="http://www.photo.rmn.fr/LowRes2/TR1/5ZRF8P/04-502724.jpg" width="529" height="650" OnError="this.src='../Images/scannotfoundTR6.jpg';">
  protected function getImagesRmn($html){
    if($matches = RegExp::searchAll($html, '<img [^>]*name="picture" [^>]*src="(http://www\.photo\.rmn\.fr[^"]*/([^/"]+\.jpg))"[^>]*>')){
      foreach($matches as $match){
        $url = $match[1];
        $name =  $match[2];
        $description = 'Agence photographique';
        $this->addImage(new Image($url, $name, $description));
      }
    } else {
      throw new Exception('no images could be parsed from url ' . $this->url . ' for type ' .  $this->type);
    }
  }

  // Duke (#4)
  // http://scriptorium.lib.duke.edu/papyrus/records/614.html
  // <A HREF="/papyrus/images/150dpi/614-at150.gif"><IMG align=middle border=1 SRC="/papyrus/images/thumbnails/614-thumb.gif">     150 dpi image of 614</A>
  protected function getImagesDuke($html){
    if($matches = RegExp::searchAll($html, '<A [^>]*HREF="([^"]+/([^/"]+\.[^/"]+))"[^>]*>( *<IMG[^>]+>)? *([^<]*150[^<]*)</A>')){
      foreach($matches as $match){
        $url = $this->generateUrl($match[1]);
        $name =  $match[2];
        $description = $match[4];
        $this->addImage(new Image($url, $name, $description));
      }
    } else {
      throw new Exception('no images could be parsed from url ' . $this->url . ' for type ' .  $this->type);
    }
  }

  // Beinecke Rare Book and Manuscript Library, Papyrus Collection (#2)
  // http://beinecke.library.yale.edu/papyrus/oneSET.asp?pid=681
  // <a href="javascript:openZOOM('http://130.132.81.65/PAPYRUSIMG/size4/D0000/z4202955.JPG','4202955')"><img src="8xmagnify.jpg" alt="See zoom size image" border="0"></a>
  // javascript:openZOOM('http://130.132.81.65/PAPYRUSIMG/size4/D0221/5868165.jpg','5868165')
  protected function getImagesYale($html){
    if($matches = RegExp::searchAll($html, '<[Aa] [Hh][Rr][Ee][Ff]="[^"]*(http://130\.132\.81\.65/PAPYRUSIMG/size4/[^"]+/([^"]+\.JPG))[^"]*"><img [^>]*[Ss][Re][Cc]="8xmagnify.jpg"[^>]*></[Aa]>')){
      foreach($matches as $match){
        $url = $this->generateUrl($match[1]);
        $name =  $match[2];
        $description = 'Beinecke Rare Book and Manuscript Library, Papyrus Collection';
        $this->addImage(new Image($url, $name, $description));
      }
    } if($matches = RegExp::searchAll($html, 'javascript:openZOOM\(\'(http://130\.132\.81\.65/PAPYRUSIMG/size4/[^\']+/(([^/>\']+)\.jpg))\', *\'[^\']+\'\)')){
      foreach($matches as $match){
        $url = $match[1];
        $name =  $match[2];
        $description = 'Beinecke Rare Book and Manuscript Library, Papyrus Collection - Zoom Image';
        $this->addImage(new Image($url, $name, $description));
      }
    } else {
      throw new Exception('no images could be parsed from url ' . $this->url . ' for type ' .  $this->type);
    }
  }

  // Glasgow University Library's Special Collections Department (#5)
  // http://special.lib.gla.ac.uk/teach/papyrus/oxyrhynchus310.html
  // <a href="../../images/papyrus/0006rwf.jpg">
  protected function getImagesGlas($html){
    if($matches = RegExp::searchAll($html, '<a [^>]*href="(../../images/papyrus/([^"]+\.jpg))"[^>]*>')){
      foreach($matches as $match){
        $url = $this->generateUrl($match[1]);
        $name =  $match[2];
        $description = 'Glasgow University Library’s Special Collections Department';
        $this->addImage(new Image($url, $name, $description));
      }
    } else {
      throw new Exception('no images could be parsed from url ' . $this->url . ' for type ' .  $this->type);
    }
  }

  // Dendlon (#1)
  // http://www.dendlon.de/Papyrus.html#P1
  // <img title="Papyrus Berloninensis P 25 239" style="width: 621px; height: 787px;" alt="Papyrus" src="Bilder/Kleopatra/Papyrus1.jpg">
  protected function getImagesDendlon($html){
    if($matches = RegExp::searchAll($html, '<img [^>]*alt="[^"]*Papyrus[^"]*" [^>]*src="(Bilder[^"]*/([^/"]+\.jpg))"[^>]*>')){
      foreach($matches as $match){
        $url = $this->generateUrl($match[1]);
        $name =  $match[2];
        $description = 'Dendlon';
        $this->addImage(new Image($url, $name, $description));
      }
    } else {
      throw new Exception('no images could be parsed from url ' . $this->url . ' for type ' .  $this->type);
    }
  }

  // Washington University Manuscripts Collections (#2)
  // http://library.wustl.edu/units/spec/manuscripts/papyri/wtu.inventory.445.html
  // <a href="26814.a.c.1.r.jpg"> 800x600 recto</a>
  protected function getImagesWash($html){
    if($matches = RegExp::searchAll($html, '<A [^>]*HREF= *"([^"]+\.jpg)"[^>]*>[^<]*recto[^<]*</A>')){
      foreach($matches as $match){
        $url = $this->generateUrl($match[1]);
        $name =  $match[1];
        $description = 'Washington University Manuscripts Collections';
        $this->addImage(new Image($url, $name, $description));
      }
    } else {
      throw new Exception('no images could be parsed from url ' . $this->url . ' for type ' .  $this->type);
    }
  }

  // Archaeogate (#7)
  // http://www.archaeogate.org/print/photo.php?src=1_article_1229_7.jpg
  // <img src="http://www.archaeogate.org/storage/1_article_1229_7.jpg" border="0" />
  protected function getImagesAgate($html){
    if($matches = RegExp::searchAll($html, '<img [^/>]*src="(http://www\.archaeogate\.org/storage/(.+\.jpg))"[^/>]*/>')){
      foreach($matches as $match){
        $url = $match[1];
        $name =  $match[2];
        $description = 'Archaeogate';
        $this->addImage(new Image($url, $name, $description));
      }
    } else {
      throw new Exception('no images could be parsed from url ' . $this->url . ' for type ' .  $this->type);
    }
  }

  // UCL MUSEUMS & COLLECTIONS PETRIE MUSEUM CATALOGUE  (#1)
  // http://petriecat.museums.ucl.ac.uk/dispatcher.aspx?action=search&database=ChoiceUCLPC&search=accession_number=%20%27UC32219%27
  protected function getImagesPetri($url){
    if($match = RegExp::search($url, 'accession_number=%20%27([^%]+)%27')){
      $url = 'http://petriecat.museums.ucl.ac.uk/wwwopac/wwwopac.exe?thumbnail=../object_images/full/65/' . strtolower($match[1]) . '.jpg&outputtype=image/jpeg&xsize=800&dontkeepaspectratio=0&fullimage=1';
      // used to work with »64« instead of »65« here ------------------------------------------------^
      // sometimes »shot1« needs to attached to the image name ----------------------------------------------------------------------^
      
      $name =  $match[1] . '.jpg';
      $description = 'UCL MUSEUMS & COLLECTIONS PETRIE MUSEUM CATALOGUE';
      $this->addImage(new Image($url, $name, $description));
    } else {
      throw new Exception('no images could be parsed from url ' . $this->url . ' for type ' .  $this->type);
    }
  }

  // Trierer Papyrussammlung (#4)
  // http://digipap.uni-trier.de/s125/publ/20__21.jpg
  protected function getImagesTrier($url){
    if($match = RegExp::search($url, '^http://digipap\.uni-trier\.de.+/(([^/]+)\.jpg)$')){
      $url = $url;
      $name =  $match[1];
      $description = 'Trier ' . $match[2];
      $this->addImage(new Image($url, $name, $description));
    } else {
      throw new Exception('no images could be loaded from url ' . $this->url . ' for type ' .  $this->type);
    }
  }

  // Adam Bülow-Jacobsen (#1)
  // http://www.igl.ku.dk/~bulow/Oxy1525.jpg
  protected function getImagesBulow($url){
    if($match = RegExp::search($url, '^http://www.igl.ku.dk/~bulow/([^/]+\.jpg)$')){
      $url = $url;
      $name =  $match[1];
      $description = 'Adam Bülow-Jacobsen’s Home Page';
      $this->addImage(new Image($url, $name, $description));
    } else {
      throw new Exception('no images could be loaded from url ' . $this->url . ' for type ' .  $this->type);
    }
  }

  // Maison des Sciences de l'Homme - Alsace (#91)
  // http://www.misha.fr/papyrus_bipab/pages_html/P_Cair_Masp_I_67101.html
  protected function getImagesMisha($html){
    if($matches = RegExp::searchAll($html, '<a +href="([^"]+/([^/"]+.jpg))"[^>]*> *Grande image *</a>')){
      foreach($matches as $match){
        $url = $this->generateUrl($match[1]);
        $name =  $match[2];
        $description = 'Maison des Sciences de l\'Homme - Alsace';
        $this->addImage(new Image($url, $name, $description));
      }
    } else {
      throw new Exception('no images could be parsed from url ' . $this->url . ' for type ' .  $this->type);
    }
  }

  // P.Count – Counting the People in Hellenistic Egypt (#1)
  // http://pcount.arts.kuleuven.ac.be/plates.html
  // <a href="75/PCount47fr1.jpg">75 dpi</a>
  protected function getImagesPcount($html){
    $documentNumber = preg_replace('/[^\d]/', '', $this->ddb);
    if($matches = RegExp::searchAll($html, '<a [^>]*href="([^"]+/(PCount' . $documentNumber . '[^"/]+.jpg))"[^>]*>([^<]+)</a>')){
      foreach($matches as $match){
        $url = $this->generateUrl($match[1]);
        $name =  $match[2];
        $description = $match[3];
        $this->addImage(new Image($url, $name, $description));
      }
    } else {
      throw new Exception('no images could be parsed from url ' . $this->url . ' for type ' .  $this->type);
    }
  }

  // Papyri aus der Sammlung Gradenwitz im Kloster Beuron und ein Katalog der Gradenwitz-Papyri aus dem Jahre 1935 (#2)
  // http://www.rzuser.uni-heidelberg.de/~gv0/Papyri/Grad.html
  // <LI>P.Grad. inv. 157 (= P.Grad. 8 = SB III 6281) <UL>  <LI><A HREF="Grad/157/157R72.jpg" TARGET="Neu">Rekto 72 DPI</A></LI>  <LI><A HREF="Grad/157/157R150.jpg" TARGET="Neu">Rekto 150 DPI</A></LI>  <LI><A HREF="Grad/157/157V72.jpg" TARGET="Neu">Verso 72 DPI</A></LI>  <LI><A HREF="Grad/157/157V150.jpg" TARGET="Neu">Verso 150 DPI</A></LI>
  protected function getImagesHdgrad($html){
    $documentNumber = preg_replace('/[^\d]/', '', $this->ddb); 

    if($match = RegExp::search($html, '<LI>P\.Grad\. inv\. \d+ \([^\)]+P\.Grad\. ' . $documentNumber . '[^\)]+\)[^<]+<UL>(.+)*?</UL>')){
      if($matches = RegExp::searchAll($match[1], '<A [^>]*HREF="(Grad/\d+/(\d+R\d+.jpg))"[^>]*>([^<]+)</A>')){
        foreach($matches as $match){
          $url = $this->generateUrl($match[1]);
          $name =  $match[2];
          $description = $match[3];
          $this->addImage(new Image($url, $name, $description));
        }
      } else {
        throw new Exception('no images could be parsed from url ' . $this->url . ' for type ' .  $this->type . ' (using html snippet)');
      }
    } else {
      throw new Exception('no images could be parsed from url ' . $this->url . ' for type ' .  $this->type . ' (trying to parse relevant html snippet)');
    }
  }

  // Oxyrhynchus online (#187)
  // http://163.1.169.40/cgi-bin/library?a=q&r=1&hs=1&e=p-000-00---0POxy--00-0-0--0prompt-10---4------0-1l--1-en-50---20-about---00031-001-1-0utfZz-8-00&h=ded&t=1&q=3482
  // <a href="/cgi-bin/library?e=q-000-00---0POxy--00-0-0--0prompt-10---4----ded--0-1l--1-en-50---20-about-3482--00031-001-1-0utfZz-8-00&amp;a=d&amp;c=POxy&amp;cl=search&amp;d=HASH014e53fd466922e10e47774d"><img src="/gsdl/images/itext.gif" alt="View the document" title="View the document" align="absmiddle" border="0" height="21" width="16"></a>
  // <a href="/gsdl/collect/POxy/index/assoc/HASH014e/53fd4669.dir/POxy.v0049.n3482.a.01.lores.jpg" target=""><img src="/gsdl/collect/POxy/index/assoc/HASH014e/53fd4669.dir/POxy.v0049.n3482.a.01.thumb.jpg" alt="thumbnail of POxy.v0049.n3482.a.01"></a>
  protected function getImagesOxy($html){
    if(array_search($this->url, self::$OXY_MISSING_IMAGE_LIST) === FALSE){
      $html = $this->followLinkOxy($html);
  
      if($matches = RegExp::searchAll($html, '<a [^>]*href="(/gsdl/collect/POxy[^"]*/([^/"]+\.jpg))"[^>]*>[^<]*<img [^>]*thumb[^>]*>[^<]*<a [^>]*name="[^"]+"[^>]*>[^<]*</a>')){
  
        foreach($matches as $match){
          $url = $this->generateUrl($match[1]);
  
          $name =  $match[2];
          $description = 'Oxyrhynchus online';
          $this->addImage(new Image($url, $name, $description));
        }
      } else {
        throw new Exception('no images could be parsed from url ' . $this->url . ' for type ' .  $this->type . ' (trying to parse relevant html snippet)');
      }
    }
  }

  protected function followLinkOxy($html){
    if($matches = RegExp::searchAll($html, '<a [^>]*href="(/cgi-bin/library?[^"]+)">[^>]*<img [^>]*src="/gsdl/images/itext.gif"[^>]*>[^<]*</a>')){
      foreach($matches as $match){
        $url = $this->generateUrl($match[1]);
        $html = self::getHtml($url);
        break;
      }
    } else {
      throw new Exception('no follow url could be parsed ' . $this->url . ' for type ' .  $this->type);
    }
    return $html;
  }

  // Griechische Papyri der Heidelberger Papyrussammlung (#4)
  // http://www.rzuser.uni-heidelberg.de/~gv0/Papyri/P.Heid._III/225/P.Heid._III_225.html
  // <A HREF="P.Heid._III_225_(150).html">Abbildung (150 DPI)</A>
  // http://www.rzuser.uni-heidelberg.de/~gv0/Papyri/P.Heid._III/225/P.Heid._III_225_%28150%29.html
  // <IMG SRC="G4047RY.JPG">
  // <IMG SRC=G_0670.jpeg>
  // http://www.rzuser.uni-heidelberg.de/~gv0/Papyri/P.Heid._III/225/G4047RY.JPG
  protected function getImagesHd($html){
    $html = $this->followLinkHd($html);

    if($matches = RegExp::searchAll($html, '<IMG SRC="?([^"]+.[Jj][Pp][Ee]?[Gg])"?>')){
      foreach($matches as $match){
        $url = $this->generateUrl($match[1]);
        $name =  '150r_' . str_replace('jpeg', 'jpg', $match[1]);
        $description = '150 dpi not verso';
        $this->addImage(new Image($url, $name, $description));
      }
    } else {
      throw new Exception('no images could be parsed from url ' . $this->url . ' for type ' .  $this->type . ' (follow link)');
    }
  }

  protected function followLinkHd($html){
    if($matches = RegExp::searchAll($html, '<A [^>]*HREF="([^"]*.html)">([^<]*150[^<]*)</A>')){
      foreach($matches as $match){
        if(strstr($match[2], 'Verso') === FALSE){
          $url = $this->generateUrl($match[1]);
          $html = self::getHtml($url);
          break;
        }
      }
    } else {
      throw new Exception('no follow url could be parsed ' . $this->url . ' for type ' .  $this->type);
    }
    return $html;
  }

  protected static function getHtml($url){
    //$html = file_get_contents($url);

    $opts    = array('http'=>array('header' => "User-Agent:MyAgent/1.0\r\n"));
    $context = stream_context_create($opts);
    $html    = file_get_contents($url, false, $context);

    if($html === FALSE){
      //Log::message('ImageCrawler::getHtml> url could not be loaded ' . $url . ' (referring website: ' . $url . ')');
      throw new Exception('ImageCrawler::getHtml> url could not be loaded ' . $url . ' (referring website: ' . $url . ')');
    }
    return str_replace(array("\n", "\r"), '', $html);
  }

  protected function generateUrl($tail){
    if(RegExp::search($tail, '^http://')){
      return $tail;
    } else {
      return $this->urlProtocol . $this->urlHost . (strpos($tail, '/') !== 0 ? RegExp::replace($this->urlPath, '\/[^\/]+\.[^\/]+$', '/') : '') . $tail;
    }
  }

  public function saveImages($path){
    $successCounter = 0;
    $errorImages = array();
    foreach($this->images as $image){
      if(($gfx = @file_get_contents($image->url)) !== FALSE){
        $file = $path . '/' . $image->name;
        file_put_contents($file, $gfx);
        $successCounter++;
      } else {
        $errorImages[] = $image->url;
      }
    }
    if(!$successCounter){
      throw new Exception('ImageCrawler::saveImages> images could not be loaded ' . implode(', ', $errorImages) . ' (' . $this->url . ')');
    } else {
      ImageUntiffer::untiffDirectory($path);
      ImageUntiffer::ungifDirectory($path);
    }
  }
}

?>