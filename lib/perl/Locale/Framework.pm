package Locale::Framework;

use 5.006;
use strict;
use warnings;

use Locale::Framework::Dumb;

our $VERSION = '0.06';

require Exporter;
use base qw(Exporter);
use vars qw(@EXPORT);

@EXPORT = qw(_T);

my $backend=new Locale::Framework::Dumb;
my $language="en";

sub new {
  my $class=shift;
  my $self;

  $self->{"nil"}=1;
  bless $self,$class;
return $self;
}

sub init {
  my $self=shift;
  my $lang_backend;
  if (ref($self) eq "Locale::Framework") {
    $lang_backend=shift;
  }
  else {
    $lang_backend=$self;
  }
  $backend=$lang_backend;
}

sub _T {
  my $text=shift;
  if (ref($text) eq "Locale::Framework") {
    $text=shift;
  }
return $backend->translate($language,$text);
}

sub clear_cache {
    $backend->clear_cache();
}

sub language {
  my $l=shift;
  if (ref($l) eq "Locale::Framework") {
    $l=shift;
  }
  $language=$l;
}

sub set_translation {
  if (ref($_[0]) eq "Locale::Framework") {
    shift;
  }
  $backend->set_translation($language,@_);
}


1;
__END__

=head1 NAME

Locale::Framework - A module for internationalization

=head1 SYNOPSIS

  use Locale::Framework;
  use Locale::Framework::SQL;
  
  Locale::Framework::init(new Locale::Framework::SQL(
                               DSN => "dbi:Pg:dbname=zclass;host=localhost", 
                               DBUSER => "test", 
                               DBPASS => "testpass", 
                               [TABLE => "testtrans"]));
    
  print _T("This is a test");

  Locale::Framework::language("nl_NL");
  
  print _T("This is a test");

Alternative interface (using wxLocale backend as an example), which
does exactly the same. There is no local object scope, there's only
a global class (or program that is) scope. OO interface is only for 
conveniance.

  use Locale::Framework;
  use Locale::Framework::wxLocale;

  my $LOC=new Locale::Framework;

  $LOC->init(new Locale::Framework::wxLocale("./locale","test"));

  print _T("This is a test");
  $LOC->language("nl_NL");
  print _T("This is a test");




=head1 ABSTRACT

This module provides simple string based internationalization support. It
exports a '_T' function that can be used for all text that need displayed.
It can work with different backends, e.g. SQL or file based backends. 
The backend defaults to Locale::Framework::Dumb, which doesn't translate at all.

=head1 DESCRIPTION

With this module simple string based internationalization can be made through
uses of '_T' function calls. Strings will be looked up by a backend and B<can>
be subsequentially cached by the same backend. For an interface to a backend
see L<Locale::Framework::SQL|Locale::Framework::SQL>.

If you don't use C<Locale::Framework::init()> to initialize a backend, Locale::Framework defaults to
the Locale::Framework::Dumb backend. The default language that is being used is 'en',
without specifiers.

=head2 C<Locale::Framework::init(backend)> --E<gt> void

This initializes the Locale::Framework module with a supported backend. 

=head2 C<Locale::Framework::language(language)> --E<gt> void

This sets the current language to use. Languages are free to be named, but
it is recommended to use common categories as provided in ISO639, e.g. 'en',
'nl', 'no', 'pl', etc. The language is default initialized to 'en' (for category
english).

If language is set to "" (empty string), no translations must be done
by function _T().

=head2 C<_T(text)> --E<gt> string

This function looks up 'text' in the current backend and returns a translation
for 'text' given the provided language. 


This function actually B<only> calls the backend function 
C<backend->translate($language,$text)>; nothing else. The backend
must to solve the rest.

=head2 C<Locale::Framework::clear_cache()> --E<gt> void

This function can be used to inform the backend to clear it's
current cached translations.

=head2 C<Locale::Framework::set_translation(text,translation)> --E<gt> boolean

This function can be used to set a translation for a given tuple (language,text).
The current C<language> setting is used.
The backend is required to update the translation for this tuple in it's
translations base. 

If the backend does not support this functionality, it must return C<false>.
Otherwise, it must update or add (if it does not exist) the translation and 
return C<true>, if this updating succeeds.

=head2 EXPORT

_T() is exported.

=head1 SEE ALSO

L<Locale::Framework::wxLocale|Locale::Framework::wxLocale>, 
L<Locale::Framework::SQL|Locale::Framework::SQL>, 
L<Locale::Framework::Dumb|Locale::Framework::Dumb>, 
ISO639 (google works).

=head1 AUTHOR

Hans Oesterholt-Dijkema <oesterhol@cpan.org>

=head1 COPYRIGHT AND LICENSE

This library is free software; you can redistribute it and/or modify
it under LGPL terms.

=cut
