<?php

/*
 * This file is part of the HDIV bundle.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace HDIV\SecurityBundle\Form\Extension;

use HDIV\SecurityBundle\HDIVCore\HDIVConfig;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormView;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormInterface;
use HDIV\SecurityBundle\HDIVCore\DataComposer\DataComposerMemory;

/**
 * Class HDIVTypeExtension
 * @package HDIV\SecurityBundle\Form\Extension
 */
class HDIVTypeExtension extends AbstractTypeExtension
{

    protected $dataComposerMemory;
    protected $HDIVConfig;
    protected $logger;

    public function __construct(DataComposerMemory $dataComposerMemory, HDIVConfig $HDIVConfig, $logger)
    {
        $this->dataComposerMemory = $dataComposerMemory;
        $this->HDIVConfig = $HDIVConfig;
        $this->logger = $logger;
    }

     public function buildForm(FormBuilderInterface $builder, array $options)
     {
        if ($options['compound']) {

            //Creates _HDIV_STATE_ hidden field to the form
            $builder->addEventListener(FormEvents::PRE_SET_DATA, function($event) use ($builder) {

                $form = $event->getForm();

                if ($form->isRoot()) {
                    $factory = $builder->getFormFactory();
                    $field = $factory->createNamed('_HDIV_STATE_', 'hidden', '', array('auto_initialize' => false, 'mapped'=>false));
                    $form->add($field);
                }
            });

            if ($this->HDIVConfig->isEditableValidationEnabled()) {
                //Checks text and password fields inputs
                $builder->addEventListener(FormEvents::POST_SUBMIT, function ($event) {

                    foreach($event->getForm() as $key => $field) {
                        if ($field->getConfig()->getType()->getName()=='text' ||
                            $field->getConfig()->getType()->getName()=='textarea' ||
                            $field->getConfig()->getType()->getName()=='search' ||
                            $field->getConfig()->getType()->getName()=='email' ||
                            $field->getConfig()->getType()->getName()=='password') {

                            if (HDIVTypeExtension::isXSSorSQLInjection($field->getData())) {
                                $field->addError(new FormError('HDIV Validation. Invalid Characters.'));
                              //  $this->logger->info('HDIV: Editable value error.');
                            }
                        }
                    }
                });
            }
        }
    }

    /**
     * Checks if <code>$in</code> is a non malicious string with ModSecurity rules
     * @param $in
     * @return bool
     */
    public static function isXSSorSQLInjection($in){

        $isSqlInjection = false;

        $sqlInjection = array (

            "@(/\*!?|\*/|[';]--|--[\s\r\n\v\f]|(?:--[^-]*?-)|([^\-&])#.*?[\s\r\n\v\f]|;?\\x00)@",
            "@(?i:(?:\A|[^\d])0x[a-f\d]{3,}[a-f\d]*)+@",
            "@(^[\"'`´’‘;]+|[\"'`´’‘;]+$)@",
            "@(?i:(\!\=|\&\&|\|\||>>|<<|>=|<=|<>|<=>|xor|rlike|regexp|isnull)|(?:not\s+between\s+0\s+and)|(?:is\s+null)|(like\s+null)|(?:(?:^|\W)in[+\s]*\([\s\d\"]+[^()]*\))|(?:xor|<>|rlike(?:\s+binary)?)|(?:regexp\s+binary))@",
            "@(?i:([\s'\"`´’‘\(\)]*?)\b([\d\w]++)([\s'\"`´’‘\(\)]*?)(?:(?:=|<=>|r?like|sounds\s+like|regexp)([\s'\"`´’‘\(\)]*?)\2\b|(?:!=|<=|>=|<>|<|>|\^|is\s+not|not\s+like|not\s+regexp)([\s'\"`´’‘\(\)]*?)(?!\2)([\d\w]+)\b))@",
            "@(?i:(?:m(?:s(?:ysaccessobjects|ysaces|ysobjects|ysqueries|ysrelationships|ysaccessstorage|ysaccessxml|ysmodules|ysmodules2|db)|aster\.\.sysdatabases|ysql\.db)|s(?:ys(?:\.database_name|aux)|chema(?:\W*\(|_name)|qlite(_temp)?_master)|d(?:atabas|b_nam)e\W*\(|information_schema|pg_(catalog|toast)|northwind|tempdb))@",
            "@(?i:(?:\b(?:(?:s(?:ys\.(?:user_(?:(?:t(?:ab(?:_column|le)|rigger)|object|view)s|c(?:onstraints|atalog))|all_tables|tab)|elect\b.{0,40}\b(?:substring|users?|ascii))|m(?:sys(?:(?:queri|ac)e|relationship|column|object)s|ysql\.(db|user))|c(?:onstraint_type|harindex)|waitfor\b\W*?\bdelay|attnotnull)\b|(?:locate|instr)\W+\()|\@\@spid\b)|\b(?:(?:s(?:ys(?:(?:(?:process|tabl)e|filegroup|object)s|c(?:o(?:nstraint|lumn)s|at)|dba|ibm)|ubstr(?:ing)?)|user_(?:(?:(?:constrain|objec)t|tab(?:_column|le)|ind_column|user)s|password|group)|a(?:tt(?:rel|typ)id|ll_objects)|object_(?:(?:nam|typ)e|id)|pg_(?:attribute|class)|column_(?:name|id)|xtype\W+\bchar|mb_users|rownum)\b|t(?:able_name\b|extpos\W+\()))@",
            "@(?i:\b(?:(?:s(?:t(?:d(?:dev(_pop|_samp)?)?|r(?:_to_date|cmp))|u(?:b(?:str(?:ing(_index)?)?|(?:dat|tim)e)|m)|e(?:c(?:_to_time|ond)|ssion_user)|ys(?:tem_user|date)|ha(1|2)?|oundex|chema|ig?n|pace|qrt)|i(?:s(null|_(free_lock|ipv4_compat|ipv4_mapped|ipv4|ipv6|not_null|not|null|used_lock))?|n(?:et6?_(aton|ntoa)|s(?:ert|tr)|terval)?|f(null)?)|u(?:n(?:compress(?:ed_length)?|ix_timestamp|hex)|tc_(date|time|timestamp)|p(?:datexml|per)|uid(_short)?|case|ser)|l(?:o(?:ca(?:l(timestamp)?|te)|g(2|10)?|ad_file|wer)|ast(_day|_insert_id)?|e(?:(?:as|f)t|ngth)|case|trim|pad|n)|t(?:ime(stamp|stampadd|stampdiff|diff|_format|_to_sec)?|o_(base64|days|seconds|n?char)|r(?:uncate|im)|an)|m(?:a(?:ke(?:_set|date)|ster_pos_wait|x)|i(?:(?:crosecon)?d|n(?:ute)?)|o(?:nth(name)?|d)|d5)|r(?:e(?:p(?:lace|eat)|lease_lock|verse)|o(?:w_count|und)|a(?:dians|nd)|ight|trim|pad)|f(?:i(?:eld(_in_set)?|nd_in_set)|rom_(base64|days|unixtime)|o(?:und_rows|rmat)|loor)|a(?:es_(?:de|en)crypt|s(?:cii(str)?|in)|dd(?:dat|tim)e|(?:co|b)s|tan2?|vg)|p(?:o(?:sition|w(er)?)|eriod_(add|diff)|rocedure_analyse|assword|i)|b(?:i(?:t_(?:length|count|x?or|and)|n(_to_num)?)|enchmark)|e(?:x(?:p(?:ort_set)?|tract(value)?)|nc(?:rypt|ode)|lt)|v(?:a(?:r(?:_(?:sam|po)p|iance)|lues)|ersion)|g(?:r(?:oup_conca|eates)t|et_(format|lock))|o(?:(?:ld_passwo)?rd|ct(et_length)?)|we(?:ek(day|ofyear)?|ight_string)|n(?:o(?:t_in|w)|ame_const|ullif)|(rawton?)?hex(toraw)?|qu(?:arter|ote)|(pg_)?sleep|year(week)?|d?count|xmltype|hour)\W*\(|\b(?:(?:s(?:elect\b(?:.{1,100}?\b(?:(?:length|count|top)\b.{1,100}?\bfrom|from\b.{1,100}?\bwhere)|.*?\b(?:d(?:ump\b.*\bfrom|ata_type)|(?:to_(?:numbe|cha)|inst)r))|p_(?:sqlexec|sp_replwritetovarbin|sp_help|addextendedproc|is_srvrolemember|prepare|sp_password|execute(?:sql)?|makewebtask|oacreate)|ql_(?:longvarchar|variant))|xp_(?:reg(?:re(?:movemultistring|ad)|delete(?:value|key)|enum(?:value|key)s|addmultistring|write)|terminate|xp_servicecontrol|xp_ntsec_enumdomains|xp_terminate_process|e(?:xecresultset|numdsn)|availablemedia|loginconfig|cmdshell|filelist|dirtree|makecab|ntsec)|u(?:nion\b.{1,100}?\bselect|tl_(?:file|http))|d(?:b(?:a_users|ms_java)|elete\b\W*?\bfrom)|group\b.*\bby\b.{1,100}?\bhaving|open(?:rowset|owa_util|query)|load\b\W*?\bdata\b.*\binfile|(?:n?varcha|tbcreato)r|autonomous_transaction)\b|i(?:n(?:to\b\W*?\b(?:dump|out)file|sert\b\W*?\binto|ner\b\W*?\bjoin)\b|(?:f(?:\b\W*?\(\W*?\bbenchmark|null\b)|snull\b)\W*?\()|print\b\W*?\@\@|cast\b\W*?\()|c(?:(?:ur(?:rent_(?:time(?:stamp)?|date|user)|(?:dat|tim)e)|h(?:ar(?:(?:acter)?_length|set)?|r)|iel(?:ing)?|ast|r32)\W*\(|o(?:(?:n(?:v(?:ert(?:_tz)?)?|cat(?:_ws)?|nection_id)|(?:mpres)?s|ercibility|alesce|t)\W*\(|llation\W*\(a))|d(?:(?:a(?:t(?:e(?:(_(add|format|sub))?|diff)|abase)|y(name|ofmonth|ofweek|ofyear)?)|e(?:(?:s_(de|en)cryp|faul)t|grees|code)|ump)\W*\(|bms_\w+\.\b)|(?:;\W*?\b(?:shutdown|drop)|\@\@version)\b|\butl_inaddr\b|\bsys_context\b|'(?:s(?:qloledb|a)|msdasql|dbo)'))@",
            "@\b(?i:having)\b\s+(\d{1,10}|'[^=]{1,10}')\s*?[=<>]|(?i:\bexecute(\s{1,5}[\w\.$]{1,5}\s{0,3})?\()|\bhaving\b ?(?:\d{1,10}|[\'\"][^=]{1,10}[\'\"]) ?[=<>]+|(?i:\bcreate\s+?table.{0,20}?\()|(?i:\blike\W*?char\W*?\()|(?i:(?:(select(.*?)case|from(.*?)limit|order\sby)))|exists\s(\sselect|select\Sif(null)?\s\(|select\Stop|select\Sconcat|system\s\(|\b(?i:having)\b\s+(\d{1,10})|'[^=]{1,10}')@",
            "@(?i:\bor\b ?(?:\d{1,10}|[\'\"][^=]{1,10}[\'\"]) ?[=<>]+|(?i:'\s+x?or\s+.{1,20}[+\-!<>=])|\b(?i:x?or)\b\s+(\d{1,10}|'[^=]{1,10}')|\b(?i:x?or)\b\s+(\d{1,10}|'[^=]{1,10}')\s*?[=<>])@",
            "@(?i)\b(?i:and)\b\s+(\d{1,10}|'[^=]{1,10}')\s*?[=]|\b(?i:and)\b\s+(\d{1,10}|'[^=]{1,10}')\s*?[<>]|\band\b ?(?:\d{1,10}|[\'\"][^=]{1,10}[\'\"]) ?[=<>]+|\b(?i:and)\b\s+(\d{1,10}|'[^=]{1,10}')@",
            "@(?i:\b(?:coalesce\b|root\@))@",
            "@(?i:(?:(?:s(?:t(?:d(?:dev(_pop|_samp)?)?|r(?:_to_date|cmp))|u(?:b(?:str(?:ing(_index)?)?|(?:dat|tim)e)|m)|e(?:c(?:_to_time|ond)|ssion_user)|ys(?:tem_user|date)|ha(1|2)?|oundex|chema|ig?n|pace|qrt)|i(?:s(null|_(free_lock|ipv4_compat|ipv4_mapped|ipv4|ipv6|not_null|not|null|used_lock))?|n(?:et6?_(aton|ntoa)|s(?:ert|tr)|terval)?|f(null)?)|u(?:n(?:compress(?:ed_length)?|ix_timestamp|hex)|tc_(date|time|timestamp)|p(?:datexml|per)|uid(_short)?|case|ser)|l(?:o(?:ca(?:l(timestamp)?|te)|g(2|10)?|ad_file|wer)|ast(_day|_insert_id)?|e(?:(?:as|f)t|ngth)|case|trim|pad|n)|t(?:ime(stamp|stampadd|stampdiff|diff|_format|_to_sec)?|o_(base64|days|seconds|n?char)|r(?:uncate|im)|an)|m(?:a(?:ke(?:_set|date)|ster_pos_wait|x)|i(?:(?:crosecon)?d|n(?:ute)?)|o(?:nth(name)?|d)|d5)|r(?:e(?:p(?:lace|eat)|lease_lock|verse)|o(?:w_count|und)|a(?:dians|nd)|ight|trim|pad)|f(?:i(?:eld(_in_set)?|nd_in_set)|rom_(base64|days|unixtime)|o(?:und_rows|rmat)|loor)|a(?:es_(?:de|en)crypt|s(?:cii(str)?|in)|dd(?:dat|tim)e|(?:co|b)s|tan2?|vg)|p(?:o(?:sition|w(er)?)|eriod_(add|diff)|rocedure_analyse|assword|i)|b(?:i(?:t_(?:length|count|x?or|and)|n(_to_num)?)|enchmark)|e(?:x(?:p(?:ort_set)?|tract(value)?)|nc(?:rypt|ode)|lt)|v(?:a(?:r(?:_(?:sam|po)p|iance)|lues)|ersion)|g(?:r(?:oup_conca|eates)t|et_(format|lock))|o(?:(?:ld_passwo)?rd|ct(et_length)?)|we(?:ek(day|ofyear)?|ight_string)|n(?:o(?:t_in|w)|ame_const|ullif)|(rawton?)?hex(toraw)?|qu(?:arter|ote)|(pg_)?sleep|year(week)?|d?count|xmltype|hour)\W*?\(|\b(?:(?:s(?:elect\b(?:.{1,100}?\b(?:(?:length|count|top)\b.{1,100}?\bfrom|from\b.{1,100}?\bwhere)|.*?\b(?:d(?:ump\b.*?\bfrom|ata_type)|(?:to_(?:numbe|cha)|inst)r))|p_(?:sqlexec|sp_replwritetovarbin|sp_help|addextendedproc|is_srvrolemember|prepare|sp_password|execute(?:sql)?|makewebtask|oacreate)|ql_(?:longvarchar|variant))|xp_(?:reg(?:re(?:movemultistring|ad)|delete(?:value|key)|enum(?:value|key)s|addmultistring|write)|terminate|xp_servicecontrol|xp_ntsec_enumdomains|xp_terminate_process|e(?:xecresultset|numdsn)|availablemedia|loginconfig|cmdshell|filelist|dirtree|makecab|ntsec)|u(?:nion\b.{1,100}?\bselect|tl_(?:file|http))|d(?:b(?:a_users|ms_java)|elete\b\W*?\bfrom)|group\b.*?\bby\b.{1,100}?\bhaving|open(?:rowset|owa_util|query)|load\b\W*?\bdata\b.*?\binfile|(?:n?varcha|tbcreato)r|autonomous_transaction)\b|i(?:n(?:to\b\W*?\b(?:dump|out)file|sert\b\W*?\binto|ner\b\W*?\bjoin)\b|(?:f(?:\b\W*?\(\W*?\bbenchmark|null\b)|snull\b)\W*?\()|print\b\W*?\@\@|cast\b\W*?\()|c(?:(?:ur(?:rent_(?:time(?:stamp)?|date|user)|(?:dat|tim)e)|h(?:ar(?:(?:acter)?_length|set)?|r)|iel(?:ing)?|ast|r32)\W*?\(|o(?:(?:n(?:v(?:ert(?:_tz)?)?|cat(?:_ws)?|nection_id)|(?:mpres)?s|ercibility|alesce|t)\W*?\(|llation\W*?\(a))|d(?:(?:a(?:t(?:e(?:(_(add|format|sub))?|diff)|abase)|y(name|ofmonth|ofweek|ofyear)?)|e(?:(?:s_(de|en)cryp|faul)t|grees|code)|ump)\W*?\(|bms_\w+\.\b)|(?:;\W*?\b(?:shutdown|drop)|\@\@version)\b|\butl_inaddr\b|\bsys_context\b|'(?:s(?:qloledb|a)|msdasql|dbo)'))@",
            "@([\~\!\@\#\$\%\^\&\*\(\)\-\+\=\{\}\[\]\|\:\;\"\'\´\’\‘\`\<\>].*?){8,}@",
            "@([\~\!\@\#\$\%\^\&\*\(\)\-\+\=\{\}\[\]\|\:\;\"\'\´\’\‘\`\<\>].*?){4,}@",
            "@(?i:(sleep\((\s*?)(\d*?)(\s*?)\)|benchmark\((.*?)\,(.*?)\)))@",
            "@(?i:(?i:\d[\"'`´’‘]\s+[\"'`´’‘]\s+\d)|(?:^admin\s*?[\"'`´’‘]|(\/\*)+[\"'`´’‘]+\s?(?:--|#|\/\*|{)?)|(?:[\"'`´’‘]\s*?\b(x?or|div|like|between|and)\b\s*?[+<>=(),-]\s*?[\d\"'`´’‘])|(?:[\"'`´’‘]\s*?[^\w\s]?=\s*?[\"'`´’‘])|(?:[\"'`´’‘]\W*?[+=]+\W*?[\"'`´’‘])|(?:[\"'`´’‘]\s*?[!=|][\d\s!=+-]+.*?[\"'`´’‘(].*?$)|(?:[\"'`´’‘]\s*?[!=|][\d\s!=]+.*?\d+$)|(?:[\"'`´’‘]\s*?like\W+[\w\"'`´’‘(])|(?:\sis\s*?0\W)|(?:where\s[\s\w\.,-]+\s=)|(?:[\"'`´’‘][<>~]+[\"'`´’‘]))@",
            "@(?i:(?:\sexec\s+xp_cmdshell)|(?:[\"'`´’‘]\s*?!\s*?[\"'`´’‘\w])|(?:from\W+information_schema\W)|(?:(?:(?:current_)?user|database|schema|connection_id)\s*?\([^\)]*?)|(?:[\"'`´’‘];?\s*?(?:select|union|having)\s*?[^\s])|(?:\wiif\s*?\()|(?:exec\s+master\.)|(?:union select \@)|(?:union[\w(\s]*?select)|(?:select.*?\w?user\()|(?:into[\s+]+(?:dump|out)file\s*?[\"'`´’‘]))@",
            "@(?i:(?:,.*?[)\da-f\"'`´’‘][\"'`´’‘](?:[\"'`´’‘].*?[\"'`´’‘]|\Z|[^\"'`´’‘]+))|(?:\Wselect.+\W*?from)|((?:select|create|rename|truncate|load|alter|delete|update|insert|desc)\s*?\(\s*?space\s*?\())@",
            "/(?i:(?:@.+=\s*?\(\s*?select)|(?:\d+\s*?(x?or|div|like|between|and)\s*?\d+\s*?[\-+])|(?:\/\w+;?\s+(?:having|and|x?or|div|like|between|and|select)\W)|(?:\d\s+group\s+by.+\()|(?:(?:;|#|--)\s*?(?:drop|alter))|(?:(?:;|#|--)\s*?(?:update|insert)\s*?\w{2,})|(?:[^\w]SET\s*?@\w+)|(?:(?:n?and|x?x?or|div|like|between|and|not |\|\||\&\&)[\s(]+\w+[\s)]*?[!=+]+[\s\d]*?[\"'`´’‘=()]))/",
            "@(?i:(?:^(-0000023456|4294967295|4294967296|2147483648|2147483647|0000012345|-2147483648|-2147483649|0000023456|2.2.90738585072007e-308|1e309)$))@",
            "@(?i:(?:(select|;)\s+(?:benchmark|if|sleep)\s*?\(\s*?\(?\s*?\w+))@",
            "@(?i:(?:[\s()]case\s*?\()|(?:\)\s*?like\s*?\()|(?:having\s*?[^\s]+\s*?[^\w\s])|(?:if\s?\([\d\w]\s*?[=<>~]))@",
            "@(?i:(?:alter\s*?\w+.*?character\s+set\s+\w+)|([\"'`´’‘];\s*?waitfor\s+time\s+[\"'`´’‘])|(?:[\"'`´’‘];.*?:\s*?goto))@",
            "@(?i:(?:merge.*?using\s*?\()|(execute\s*?immediate\s*?[\"'`´’‘])|(?:\W+\d*?\s*?having\s*?[^\s\-])|(?:match\s*?[\w(),+-]+\s*?against\s*?\())@",
            "/(?i:(?:union\s*?(?:all|distinct|[(!@]*?)?\s*?[([]*?\s*?select\s+)|(?:\w+\s+like\s+[\"'`´’‘])|(?:like\s*?[\"'`´’‘]\%)|(?:[\"'`´’‘]\s*?like\W*?[\"'`´’‘\d])|(?:[\"'`´’‘]\s*?(?:n?and|x?x?or|div|like|between|and|not |\|\||\&\&)\s+[\s\w]+=\s*?\w+\s*?having\s+)|(?:[\"'`´’‘]\s*?\*\s*?\w+\W+[\"'`´’‘])|(?:[\"'`´’‘]\s*?[^?\w\s=.,;)(]+\s*?[(@\"'`´’‘]*?\s*?\w+\W+\w)|(?:select\s+?[\[\]()\s\w\.,\"'`´’‘-]+from\s+)|(?:find_in_set\s*?\())/",
            "@(?i:(?:(union(.*?)select(.*?)from)))@",
            "@(?i:(?:select\s*?pg_sleep)|(?:waitfor\s*?delay\s?[\"'`´’‘]+\s?\d)|(?:;\s*?shutdown\s*?(?:;|--|#|\/\*|{)))@",
            "@(?i:(?:\[\$(?:ne|eq|lte?|gte?|n?in|mod|all|size|exists|type|slice|x?or|div|like|between|and)\]))@",
            "@(?i:(?:\)\s*?when\s*?\d+\s*?then)|(?:[\"'`´’‘]\s*?(?:#|--|{))|(?:\/\*!\s?\d+)|(?:ch(?:a)?r\s*?\(\s*?\d)|(?:(?:(n?and|x?x?or|div|like|between|and|not)\s+|\|\||\&\&)\s*?\w+\())@",
            "/(?i:(?:[\"'`´’‘]\s+and\s*?=\W)|(?:\(\s*?select\s*?\w+\s*?\()|(?:\*\/from)|(?:\+\s*?\d+\s*?\+\s*?@)|(?:\w[\"'`´’‘]\s*?(?:[-+=|@]+\s*?)+[\d(])|(?:coalesce\s*?\(|@@\w+\s*?[^\w\s])|(?:\W!+[\"'`´’‘]\w)|(?:[\"'`´’‘];\s*?(?:if|while|begin))|(?:[\"'`´’‘][\s\d]+=\s*?\d)|(?:order\s+by\s+if\w*?\s*?\()|(?:[\s(]+case\d*?\W.+[tw]hen[\s(]))/",
            "@(?i:(?:procedure\s+analyse\s*?\()|(?:;\s*?(declare|open)\s+[\w-]+)|(?:create\s+(procedure|function)\s*?\w+\s*?\(\s*?\)\s*?-)|(?:declare[^\w]+[\@#]\s*?\w+)|(exec\s*?\(\s*?\@))@",
            "/(?i:(?:[\"'`´’‘]\s*?(x?or|div|like|between|and)\s*?[\"'`´’‘]?\d)|(?:\\\\x(?:23|27|3d))|(?:^.?[\"'`´’‘]$)|(?:(?:^[\"'`´’‘\\\\]*?(?:[\d\"'`´’‘]+|[^\"'`´’‘]+[\"'`´’‘]))+\s*?(?:n?and|x?x?or|div|like|between|and|not|\|\||\&\&)\s*?[\w\"'`´’‘][+&!@(),.-])|(?:[^\w\s]\w+\s*?[|-]\s*?[\"'`´’‘]\s*?\w)|(?:@\w+\s+(and|x?or|div|like|between|and)\s*?[\"'`´’‘\d]+)|(?:@[\w-]+\s(and|x?or|div|like|between|and)\s*?[^\w\s])|(?:[^\w\s:]\s*?\d\W+[^\w\s]\s*?[\"'`´’‘].)|(?:\Winformation_schema|table_name\W))/",
            "/(?i:(?:in\s*?\(+\s*?select)|(?:(?:n?and|x?x?or|div|like|between|and|not |\|\||\&\&)\s+[\s\w+]+(?:regexp\s*?\(|sounds\s+like\s*?[\"'`´’‘]|[=\d]+x))|([\"'`´’‘]\s*?\d\s*?(?:--|#))|(?:[\"'`´’‘][\%&<>^=]+\d\s*?(=|x?or|div|like|between|and))|(?:[\"'`´’‘]\W+[\w+-]+\s*?=\s*?\d\W+[\"'`´’‘])|(?:[\"'`´’‘]\s*?is\s*?\d.+[\"'`´’‘]?\w)|(?:[\"'`´’‘]\|?[\w-]{3,}[^\w\s.,]+[\"'`´’‘])|(?:[\"'`´’‘]\s*?is\s*?[\d.]+\s*?\W.*?[\"'`´’‘]))/",
            "/(?i:(?:create\s+function\s+\w+\s+returns)|(?:;\s*?(?:select|create|rename|truncate|load|alter|delete|update|insert|desc)\s*?[\[(]?\w{2,}))/",
            "/(?i:(?:[\d\W]\s+as\s*?[\"'`´’‘\w]+\s*?from)|(?:^[\W\d]+\s*?(?:union|select|create|rename|truncate|load|alter|delete|update|insert|desc))|(?:(?:select|create|rename|truncate|load|alter|delete|update|insert|desc)\s+(?:(?:group_)concat|char|load_file)\s?\(?)|(?:end\s*?\);)|([\"'`´’‘]\s+regexp\W)|(?:[\s(]load_file\s*?\())/"
           );

        foreach ($sqlInjection as &$valor) {
            if (preg_match($valor, $in)) {
                $isSqlInjection = true;
            }
        }

        if ($isSqlInjection) {
            return true;
        }

        $isXSS = false;
        $xss = array (

            "@(?i)(<script[^>]*>[\s\S]*?<\/script[^>]*>|<script[^>]*>[\s\S]*?<\/script[[\s\S]]*[\s\S]|<script[^>]*>[\s\S]*?<\/script[\s]*[\s]|<script[^>]*>[\s\S]*?<\/script|<script[^>]*>[\s\S]*?)@",
            "@(?i)([\s\"'`;\/0-9\=]+on\w+\s*=)@",
            "/(?i)((?:=|U\s*R\s*L\s*\()\s*[^>]*\s*S\s*C\s*R\s*I\s*P\s*T\s*:|&colon;|[\s\S]allowscriptaccess[\s\S]|[\s\S]src[\s\S]|[\s\S]data:text\/html[\s\S]|[\s\S]xlink:href[\s\S]|[\s\S]base64[\s\S]|[\s\S]xmlns[\s\S]|[\s\S]xhtml[\s\S]|[\s\S]style[\s\S]|<style[^>]*>[\s\S]*?|[\s\S]@import[\s\S]|<applet[^>]*>[\s\S]*?|<meta[^>]*>[\s\S]*?|<object[^>]*>[\s\S]*?)/",
            "/\bgetparentfolder\b/",
            "/\bonmousedown\b\W*?\=/",
            "/\bsrc\b\W*?\bshell:/",
            "/\bmocha:/",
            "/\bonabort\b/",
            "/\blowsrc\b\W*?\bhttp:/",
            "/\bonmouseup\b\W*?\=/",
            "/\bstyle\b\W*\=.*bexpression\b\W*\(/",
            "/\bhref\b\W*?\bshell:/",
            "/\bcreatetextrange\b/",
            "/\bondragdrop\b\W*?\=/",
            "/\bcopyparentfolder\b/",
            "/\bonunload\b\W*?\=/",
            "/\.execscript\b/",
            "/\bgetspecialfolder\b/",
            "/<body\b.*?\bonload\b/",
            "/\burl\b\W*?\bvbscript:/",
            "/\bonkeydown\b\W*?\=/",
            "/\bonmousemove\b\W*?\=/",
            "/\blivescript:/",
            "/\bonblur\b\W*?\=/",
            "/\bonmove\b\W*?\=/",
            "/\bsettimeout\b\W*?\(/",
            "/\< ?iframe/",
            "/\bsrc\b\W*?\bjavascript:/",
            "/<body\b.*?\bbackground\b/",
            "/\bsrc\b\W*?\bvbscript:/",
            "/\btype\b\W*?\btext\b\W*?\becmascript\b/",
            "/\bonfocus\b\W*?\=/",
            "/\bdocument\b\s*\.\s*\bcookie\b/",
            "/\<\!\[cdata\[/",
            "/\bonerror\b\W*?\=/",
            "/\blowsrc\b\W*?\bjavascript:/",
            "/\bactivexobject\b/",
            "/\bonkeypress\b\W*?\=/",
            "/\bonsubmit\b\W*?\=/",
            "/\btype\b\W*?\bapplication\b\W*?\bx-javascript\b/",
            "/\.addimport\b/",
            "/\bhref\b\W*?\bjavascript:/",
            "/\bonchange\b\W*?\=/",
            "/\btype\b\W*?\btext\b\W*?\bjscript\b/",
            "/\balert\b\W*?\(/",
            "/\btype\b\W*?\bapplication\b\W*?\bx-vbscript\b/",
            "/\< ?meta\b/",
            "/\bsrc\b\W*?\bhttp:/",
            "/\btype\b\W*?\btext\b\W*?\bvbscript\b/",
            "/\bonmouseout\b\W*?\=/",
            "/\blowsrc\b\W*?\bshell:/",
            "/\basfunction:/",
            "/\bonmouseover\b\W*?\=/",
            "/\bhref\b\W*?\bvbscript:/",
            "/\burl\b\W*?\bjavascript:/",
            "/\.innerhtml\b/",
            "/\bonselect\b\W*?\=/",
            "/\@import\b/",
            "/\blowsrc\b\W*?\bvbscript:/",
            "/\bonload\b\W*?\=/",
            "/\< ?script\b/",
            "/\bonresize\b\W*?\=/",
            "/\bonclick\b\W*?\=/",
            "/\biframe\b.{0,100}?\bsrc\b/",
            "/\bbackground-image:/",
            "/\bonkeyup\b\W*?\=/",
            "/<input\b.*?\btype\b\W*?\bimage\b/",
            "/\burl\b\W*?\bshell:/",
            "/\btype\b\W*?\btext\b\W*?\bjavascript\b/",
            "/\.fromcharcode\b/",
            "/<(a|abbr|acronym|address|applet|area|audioscope|b|base|basefront|bdo|bgsound|big|blackface|blink|blockquote|body|bq|br|button|caption|center|cite|code|col|colgroup|comment|dd|del|dfn|dir|div|dl|dt|em|embed|fieldset|fn|font|form|frame|frameset|h1|head|hr|html|i|iframe|ilayer|img|input|ins|isindex|kdb|keygen|label|layer|legend|li|limittext|link|listing|map|marquee|menu|meta|multicol|nobr|noembed|noframes|noscript|nosmartquotes|object|ol|optgroup|option|p|param|plaintext|pre|q|rt|ruby|s|samp|script|select|server|shadow|sidebar|small|spacer|span|strike|strong|style|sub|sup|table|tbody|td|textarea|tfoot|th|thead|title|tr|tt|u|ul|var|wbr|xml|xmp)\W/",
            "/\ballowscriptaccess\b|\brel\b\W*?=/",
            "@.+application/x-shockwave-flash|image/svg\+xml|text/(css|html|ecmascript|javascript|vbscript|x-(javascript|scriptlet|vbscript)).+@",
            "/\bon(abort|blur|change|click|dblclick|dragdrop|error|focus|keydown|keypress|keyup|load|mousedown|mousemove|mouseout|mouseover|mouseup|move|readystatechange|reset|resize|select|submit|unload)\b\W*?=/",
            "/\b(background|dynsrc|href|lowsrc|src)\b\W*?=/",
            "/(asfunction|javascript|vbscript|data|mocha|livescript):/",
            "/\bstyle\b\W*?=/",
            "/(fromcharcode|alert|eval)\s*\(/",
            "/background\b\W*?:\W*?url|background-image\b\W*?:|behavior\b\W*?:\W*?url|-moz-binding\b|@import\b|expression\b\W*?\(/",
            "/<!\[cdata\[|\]\]>/",
            "@[/'\"<]xss[/'\">]@",
            "/(88,83,83)/",
            "/'';!--\"<xss>=&{()}/",
            "/&{/",
            "/<!(doctype|entity)/",
            "/(?i:<script.*?>)/",
            "/(?i:<style.*?>.*?((@[i\\\\])|(([:=]|(&#x?0*((58)|(3A)|(61)|(3D));?)).*?([(\\\\]|(&#x?0*((40)|(28)|(92)|(5C));?)))))/",
            "@(?i:<script.*?[ /+\t]*?((src)|(xlink:href)|(href))[ /+\t]*=)@",
            "@(?i:<[i]?frame.*?[ /+\t]*?src[ /+\t]*=)@",
            "@(?i:<.*[:]vmlframe.*?[ /+\t]*?src[ /+\t]*=)@",
            "@(?i:(j|(&#x?0*((74)|(4A)|(106)|(6A));?))([\t]|(&((#x?0*(9|(13)|(10)|A|D);?)|(tab;)|(newline;))))*(a|(&#x?0*((65)|(41)|(97)|(61));?))([\t]|(&((#x?0*(9|(13)|(10)|A|D);?)|(tab;)|(newline;))))*(v|(&#x?0*((86)|(56)|(118)|(76));?))([\t]|(&((#x?0*(9|(13)|(10)|A|D);?)|(tab;)|(newline;))))*(a|(&#x?0*((65)|(41)|(97)|(61));?))([\t]|(&((#x?0*(9|(13)|(10)|A|D);?)|(tab;)|(newline;))))*(s|(&#x?0*((83)|(53)|(115)|(73));?))([\t]|(&((#x?0*(9|(13)|(10)|A|D);?)|(tab;)|(newline;))))*(c|(&#x?0*((67)|(43)|(99)|(63));?))([\t]|(&((#x?0*(9|(13)|(10)|A|D);?)|(tab;)|(newline;))))*(r|(&#x?0*((82)|(52)|(114)|(72));?))([\t]|(&((#x?0*(9|(13)|(10)|A|D);?)|(tab;)|(newline;))))*(i|(&#x?0*((73)|(49)|(105)|(69));?))([\t]|(&((#x?0*(9|(13)|(10)|A|D);?)|(tab;)|(newline;))))*(p|(&#x?0*((80)|(50)|(112)|(70));?))([\t]|(&((#x?0*(9|(13)|(10)|A|D);?)|(tab;)|(newline;))))*(t|(&#x?0*((84)|(54)|(116)|(74));?))([\t]|(&((#x?0*(9|(13)|(10)|A|D);?)|(tab;)|(newline;))))*(:|(&((#x?0*((58)|(3A));?)|(colon;)))).)@",
            "@(?i:(v|(&#x?0*((86)|(56)|(118)|(76));?))([\t]|(&((#x?0*(9|(13)|(10)|A|D);?)|(tab;)|(newline;))))*(b|(&#x?0*((66)|(42)|(98)|(62));?))([\t]|(&((#x?0*(9|(13)|(10)|A|D);?)|(tab;)|(newline;))))*(s|(&#x?0*((83)|(53)|(115)|(73));?))([\t]|(&((#x?0*(9|(13)|(10)|A|D);?)|(tab;)|(newline;))))*(c|(&#x?0*((67)|(43)|(99)|(63));?))([\t]|(&((#x?0*(9|(13)|(10)|A|D);?)|(tab;)|(newline;))))*(r|(&#x?0*((82)|(52)|(114)|(72));?))([\t]|(&((#x?0*(9|(13)|(10)|A|D);?)|(tab;)|(newline;))))*(i|(&#x?0*((73)|(49)|(105)|(69));?))([\t]|(&((#x?0*(9|(13)|(10)|A|D);?)|(tab;)|(newline;))))*(p|(&#x?0*((80)|(50)|(112)|(70));?))([\t]|(&((#x?0*(9|(13)|(10)|A|D);?)|(tab;)|(newline;))))*(t|(&#x?0*((84)|(54)|(116)|(74));?))([\t]|(&((#x?0*(9|(13)|(10)|A|D);?)|(tab;)|(newline;))))*(:|(&((#x?0*((58)|(3A));?)|(colon;)))).)@",
            "@(?i:<EMBED[ /+\t].*?((src)|(type)).*?=)@",
            "@(?i:<[?]?import[ /+\t].*?implementation[ /+\t]*=)@",
            "@(?i:<META[ /+\t].*?http-equiv[ /+\t]*=[ /+\t]*[\"\'`]?(((c|(&#x?0*((67)|(43)|(99)|(63));?)))|((r|(&#x?0*((82)|(52)|(114)|(72));?)))|((s|(&#x?0*((83)|(53)|(115)|(73));?)))))@",
            "@(?i:<META[ /+\t].*?charset[ /+\t]*=)@",
            "@(?i:<LINK[ /+\t].*?href[ /+\t]*=)@",
            "@(?i:<BASE[ /+\t].*?href[ /+\t]*=)@",
            "@(?i:<APPLET[ /+\t>])@",
            "@(?i:<OBJECT[ /+\t].*?((type)|(codetype)|(classid)|(code)|(data))[ /+\t]*=)@",
            "@(?i:[\"\'].*?[,].*(((v|(\\\\u0076)|(\\166)|(\\x76))[^a-z0-9]*(a|(\\\\u0061)|(\\141)|(\\x61))[^a-z0-9]*(l|(\\\\u006C)|(\\154)|(\\x6C))[^a-z0-9]*(u|(\\\\u0075)|(\\165)|(\\x75))[^a-z0-9]*(e|(\\\\u0065)|(\\145)|(\\x65))[^a-z0-9]*(O|(\\\\u004F)|(\\117)|(\\x4F))[^a-z0-9]*(f|(\\\\u0066)|(\\146)|(\\x66)))|((t|(\\\\u0074)|(\\164)|(\\x74))[^a-z0-9]*(o|(\\\\u006F)|(\\157)|(\\x6F))[^a-z0-9]*(S|(\\\\u0053)|(\\123)|(\\x53))[^a-z0-9]*(t|(\\\\u0074)|(\\164)|(\\x74))[^a-z0-9]*(r|(\\\\u0072)|(\\162)|(\\x72))[^a-z0-9]*(i|(\\\\u0069)|(\\151)|(\\x69))[^a-z0-9]*(n|(\\\\u006E)|(\\156)|(\\x6E))[^a-z0-9]*(g|(\\\\u0067)|(\\147)|(\\x67)))).*?:)@",
            "@(?i:[\"\'][ ]*(([^a-z0-9~_:\' ])|(in)).+?\(.*?\))@",
            "@(?i:[\"\'].*?\)[ ]*(([^a-z0-9~_:\' ])|(in)).+?\()@",
            "@(?i:[\"\'][ ]*(([^a-z0-9~_:\' ])|(in)).+?[.].+?=)@",
            "@(?i:[\"\'][ ]*(([^a-z0-9~_:\' ])|(in)).+?[\[].*?[\]].*?=)@",
            "@(?i:[\"\'][ ]*(([^a-z0-9~_:\' ])|(in)).*?(((l|(\\\\u006C))(o|(\\\\u006F))(c|(\\\\u0063))(a|(\\\\u0061))(t|(\\\\u0074))(i|(\\\\u0069))(o|(\\\\u006F))(n|(\\\\u006E)))|((n|(\\\\u006E))(a|(\\\\u0061))(m|(\\\\u006D))(e|(\\\\u0065)))|((o|(\\\\u006F))(n|(\\\\u006E))(e|(\\\\u0065))(r|(\\\\u0072))(r|(\\\\u0072))(o|(\\\\u006F))(r|(\\\\u0072)))|((v|(\\\\u0076))(a|(\\\\u0061))(l|(\\\\u006C))(u|(\\\\u0075))(e|(\\\\u0065))(O|(\\\\u004F))(f|(\\\\u0066)))).*?=)@",
            "@(?i:<form.*?>)@",
            "@(?i:<isindex[ /+\t>])@",
            "@(?i:[ /+\t\"\'`]style[ /+\t]*?=.*([:=]|(&#x?0*((58)|(3A)|(61)|(3D));?)).*?([(\\\\]|(&#x?0*((40)|(28)|(92)|(5C));?)))@",
            "@(?i:[ /+\t\"\'`]on\[a-z]\[a-z]\[a-z]+?[ +\t]*?=.)@",
            "@(?i:[ /+\t\"\'`]datasrc[ +\t]*?=.)@",
        );


        foreach ($xss as &$valor) {

            if (preg_match($valor, $in)) {
                $isXSS = true;
            }

        }

        if ($isXSS) {
            return true;
        }


        return false;
    }


    public function finishView(FormView $view, FormInterface $form, array $options)
    {
        if ($options['compound']) {

            //Store all form fields and set the _HDIV_STATE_ value to the hidden field.
            $action = $form->getConfig()->getAction();
            $action = $this->removeParam($action, '_HDIV_STATE_');
            $view->vars['action'] = $action;
            $token = $this->dataComposerMemory->addFormToCurrentPage($form, $action);

            if ($form->isRoot()) {
                $view->children['_HDIV_STATE_']->vars['value'] = $token;
            }

           // $this->toString($form);
         }
    }


    public function getExtendedType()
    {
        return 'form';
    }

    public function toString(FormInterface $form)
    {
        $children = $form->all();

        print("<br/>FORM CHILDREN<br/>");
        print("<br/> FORM TYPE: ".$form->getConfig()->getType()->getName()."<br/>");
        print("<br/> FORM NAME: ".$form->getName()."<br/>");
        print("<br/>");

        foreach ($children as $ch) {
            print("Name: ".$ch->getName() . "<br/>");
            print("Type: ".$ch->getConfig()->getType()->getName() . "<br/>");
            print("<br/>");
            if ($ch->all()) {
                $this->toString($ch);
            }
        }
        print(" FORM END: ".$form->getName()."<br/>");
        print("<br/>");
        print("<br/>");
    }


    /**
     * Removes a param from a given url
     *
     * @param $url
     * @param $param
     * @return mixed
     */
    function removeParam($url, $param) {
        $url = preg_replace('/(&|\?)'.preg_quote($param).'=[^&]*$/', '', $url);
        $url = preg_replace('/(&|\?)'.preg_quote($param).'=[^&]*&/', '$1', $url);
        return $url;
    }

}