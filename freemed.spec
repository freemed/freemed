#	$Id$
#	$Author$
#	$Revision$

Name:		freemed
Summary:	Opensource electronic medical record (EMR) software
Version:	0.7.1
Release:	1fc1
License:	GPL
Group:		Applications/Medical
URL:		http://www.freemed.org/
BuildArch:	noarch

Source0:	%{name}-%{version}.tar.gz

BuildRoot:	%{_tmppath}/%{name}-%{version}-%{release}-root

Requires:	php >= 4.3.0, php-mysql >= 4.3.0, phpwebtools >= 0.4.2, tetex, ghostscript, mkisofs, cdrecord, netpbm-progs, ImageMagick, cups, djvulibre
BuildPrereq:	make, perl

%description
FreeMED is an opensource electronic medical record (EMR) and medical
management system.

%prep

%setup

%build
make all

%install
rm -fr %{buildroot}
mkdir -p %{buildroot}%{_prefix}/share/freemed/
%makeinstall
# Install seperate init script

%clean
rm -fr %{buildroot}

%post

%preun

%postun

%files
%defattr(-,root,root)
%{_datadir}/%{name}

%changelog

* Mon Oct 18 2004 Jeff Buchbinder <jeff@freemedsoftware.com> - 0.7.1-1fc1
  - v0.7.1 release

* Fri May 28 2004 Jeff Buchbinder <jeff@freemedsoftware.com> - 0.7.0-1fc1
  - v0.7.0 release

* Fri Apr 16 2004 Jeff Buchbinder <jeff@freemedsoftware.com> - 0.7.0b3-1fc1
  - Initial Fedora Core 1 packaging (apologies to RH9 users)

