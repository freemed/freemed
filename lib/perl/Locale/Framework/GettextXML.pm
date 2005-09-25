package Locale::Framework::GettextXML;

use strict;
use XML::RAX;

our $VERSION='0.01';

sub new {
  my $class=shift;
  my $locale_path_prefix=shift  or die "You need to specify a locale path prefix";;
  my $catalog=shift or die "You need to specify a catalog to use";
  my $debug=shift or 0;
  my $self;

  $self->{"path"}=$locale_path_prefix;
  $self->{"catalog"}=$catalog;
  $self->{"lang"}="default";
  $self->{"debug"}=$debug;
  $self->{"mark_untranslated"}=$debug;

  bless $self,$class;
  return $self;
}

sub translate {
  my ($self,$lang,$text)=@_;
  print "[debug] called translate\n" if ($self->{"debug"});
  if ($self->{"lang"} ne $lang) {
    undef $self->{"cache"};
    if ($lang eq "") {
      # Return source text if this is null
      print "[debug] language = undef\n" if ($self->{"debug"});
      return ($self->{"mark_untranslated"} ? '*' : '' ).$text;
    } else {
      print "[debug] load catalog $lang\n" if ($self->{"debug"});
      $self->load_catalog($lang);
    }
    $self->{"lang"}=$lang;
  }
  return $self->gettextxml($text);
}

sub load_catalog {
  my ($self, $lang) = @_;
  print "[debug] called load_catalog\n" if ($self->{"debug"});
  # Unset current catalog
  if ($lang ne "") {
    my $path = $self->{"path"}."/".$lang."/".$self->{"catalog"}.".xml";
    if (-f $path) {
      my $rax = new XML::RAX();
      print "[debug] opening file $path\n" if ($self->{"debug"});
      $rax->openfile($path);
      $rax->setRecord('translation');
      while (my $rec = $rax->readRecord()) {
        # Cache entries
	$self->{"cache"}->{$rec->getField('original')} = $rec->getField('translated');
      }
    } else {
      # Don't load anything if we can't read the file
      print "[debug] could not load $path\n" if ($self->{"debug"});
      return 0;
    }
  }
}

sub gettextxml {
  my ($self, $phrase) = @_;
  print "[debug] called gettextxml ( $phrase )\n" if ($self->{"debug"});
  return $self->{"cache"}->{$phrase} if ($self->{"cache"}->{$phrase});
  # Return source text if this is null
  return ($self->{"mark_untranslated"} ? '*' : '' ).$phrase;
}

sub set_translation {
  return 0;
}

sub clear_cache {
}

my @languages=(
"ab",
"aa",
"af_ZA",
"sq_AL",
"am",
"ar",
"ar_DZ",
"ar_BH",
"ar_EG",
"ar_IQ",
"ar_JO",
"ar_KW",
"ar_LB",
"ar_LY",
"ar_MA",
"ar_OM",
"ar_QA",
"ar_SA",
"ar_SD",
"ar_SY",
"ar_TN",
"ar_AE",
"ar_YE",
"hy",
"as",
"ay",
"az",
"az",
"az",
"ba",
"eu_ES",
"be_BY",
"bn",
"dz",
"bh",
"bi",
"br",
"bg_BG",
"my",
"km",
"ca_ES",
"zh_CN",
"zh_CN",
"zh_TW",
"zh_HK",
"zh_MO",
"zh_SG",
"zh_TW",
"co",
"hr_HR",
"cs_CZ",
"da_DK",
"nl_NL",
"nl_BE",
"en_GB",
"en_GB",
"en_US",
"en_AU",
"en_BZ",
"en_BW",
"en_CA",
"en_CB",
"en_DK",
"en_IE",
"en_JM",
"en_NZ",
"en_PH",
"en_ZA",
"en_TT",
"en_ZW",
"eo",
"et_EE",
"fo_FO",
"fa_IR",
"fj",
"fi_FI",
"fr_FR",
"fr_BE",
"fr_CA",
"fr_LU",
"fr_MC",
"fr_CH",
"fy",
"gl_ES",
"ka",
"de_DE",
"de_AT",
"de_BE",
"de_LI",
"de_LU",
"de_CH",
"el_GR",
"kl_GL",
"gn",
"gu",
"ha",
"he_IL",
"hi_IN",
"hu_HU",
"is_IS",
"id_ID",
"ia",
"ie",
"iu",
"ik",
"ga_IE",
"it_IT",
"it_CH",
"ja_JP",
"jw",
"kn",
"ks",
"ks_IN",
"kk",
"kw_GB",
"rw",
"ky",
"rn",
"konkani",
"ko_KR",
"ku",
"lo",
"la",
"lv_LV",
"ln",
"lt_LT",
"mk_MK",
"mg",
"ms_MY",
"ml",
"ms_BN",
"ms_MY",
"mt_MT",
"manipuri",
"mi",
"mr_IN",
"mo",
"mn",
"na",
"ne",
"ne_IN",
"nb_NO",
"nn_NO",
"oc",
"or",
"om",
"ps",
"pl_PL",
"pt_PT",
"pt_BR",
"pa",
"qu",
"rm",
"ro_RO",
"ru_RU",
"ru_UA",
"sm",
"sg",
"sa",
"gd",
"sr_YU",
"sr_YU",
"sr_YU",
"sh",
"st",
"tn",
"sn",
"sd",
"si",
"ss",
"sk_SK",
"sl_SI",
"so",
"es_ES",
"es_AR",
"es_BO",
"es_CL",
"es_CO",
"es_CR",
"es_DO",
"es_EC",
"es_SV",
"es_GT",
"es_HN",
"es_MX",
"es_ES",
"es_NI",
"es_PA",
"es_PY",
"es_PE",
"es_PR",
"es_UY",
"es_US",
"es_VE",
"su",
"sw_KE",
"sv_SE",
"sv_FI",
"tl",
"tg",
"ta",
"tt",
"te",
"th_TH",
"bo",
"ti",
"to",
"ts",
"tr_TR",
"tk",
"tw",
"ug",
"uk_UA",
"ur",
"ur_IN",
"ur_PK",
"uz",
"uz",
"uz",
"vi_VN",
"vo",
"cy",
"wo",
"xh",
"yi",
"yo",
"za",
"zu"
);

1;

=head1 NAME

Locale::Framework::GettextXML, a GettextXML backend for Locale::Framework

=head1 SYNOPSIS

  use Locale::Framework;
  use Locale::Framework::GettextXML;
  
  my $wxloc=new Locale::Framework::GettextXML('locale', 'myprog');
  Locale::Framework::language("en");

  print _T("This is a test");

  Locale::Framework::language("nl");
  
  print _T("This is a test");

=head1 ABSTRACT

This module provides a GettextXML backend for the Locale::Framework 
internationalization module.

=head1 DESCRIPTION

=head2 C<new(locale_prefix, catalog[, debug])> --E<gt> Locale::Framework::GettextXML

Instantiates a new backend object. Optionally can "mark" untranslated strings
and print debugging text.

=head2 C<translate(language,text)> --E<gt> string

This function looks up a translation for the tuple (language, text)
via GettextXML. 

=head2 C<set_translation(language,text,translation)> --E<gt> boolean

This function returns false for this backend, because GettextXML
does not support dynamic updating yet.

=head2 C<clear_cache()> --E<gt> void

This function is a noop for this backend.

=head1 SEE ALSO

L<Locale::Framework|Locale::Framework>

=head1 AUTHOR

Jeff Buchbinder <freemed@gmail.com>

=head1 COPYRIGHT AND LICENSE

This library is free software; you can redistribute it and/or modify
it under LGPL terms.

=cut
