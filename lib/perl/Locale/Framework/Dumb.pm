package Locale::Framework::Dumb;

use strict;

sub new {
    my $class=shift;
    my $self;
    $self->{"dumb"}=1;
    bless $self,$class;
return $self;
}

sub translate {
  my ($self,$lang,$text)=@_;
return $text;
}

sub clear_cache {
}

sub set_translation {
return 0;
}



1;

=head1 NAME

Locale::Framework::Dumb - A backend for Locale::Framework internationalization

=head1 SYNOPSIS

  use Locale::Framework;

  print _T("This is a test");

  Locale::Framework::language("nl");
  
  print _T("This is a test");

=head1 ABSTRACT

This module provides a Dumb backend for the Locale::Framework internationalization
module.

=head1 DESCRIPTION

=head2 C<new()> --E<gt> Locale::Framework::Dumb

Instantiates a new Locale::Framework::Dumb backend.

=head2 C<translate(language,text)> --E<gt> string

Returns 'text'.

=head2 C<clear_cache()> --E<gt> void

Does nothing.


=head1 SEE ALSO

L<Locale::Framework|Locale::Framework>.

=head1 AUTHOR

Hans Oesterholt-Dijkema <oesterhol@cpan.org>

=head1 COPYRIGHT AND LICENSE

This library is free software; you can redistribute it and/or modify
it under LGPL terms.

=cut
