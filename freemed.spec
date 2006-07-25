#	$Id$
#	$Author$
#	$Revision$

Name:		freemed
Summary:	Opensource electronic medical record (EMR) software
Version:	0.8.3
Release:	1
License:	GPL
Group:		Applications/Medical
URL:		http://www.freemedsoftware.org/
BuildArch:	noarch

Source0:	%{name}-%{version}.tar.gz

BuildRoot:	%{_tmppath}/%{name}-%{version}-%{release}-root

Requires:	php >= 4.3.0, php-mysql >= 4.3.0, tetex, ghostscript, mkisofs, cdrecord, netpbm-progs, ImageMagick, cups, djvulibre
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

* Tue Feb 25 2006 Jeff Buchbinder <jeff@freemedsoftware.org> - 0.8.3-1
  - v0.8.3 release

* Sun Feb 19 2006 Jeff Buchbinder <jeff@freemedsoftware.com> - 0.8.2-1
  - v0.8.2 release

* Thu Nov 17 2005 Jeff Buchbinder <jeff@freemedsoftware.com> - 0.8.1.1-1
  - v0.8.1.1 release

* Sun Oct 30 2005 Jeff Buchbinder <jeff@freemedsoftware.com> - 0.8.1-1
  - v0.8.1 release

* Sat Aug 27 2005 Jeff Buchbinder <jeff@freemedsoftware.com> - 0.8.0-1
  - v0.8.0 release

* Tue Nov 30 2004 Jeff Buchbinder <jeff@freemedsoftware.com> - 0.7.2-1fc1
  - v0.7.1 release

* Mon Oct 18 2004 Jeff Buchbinder <jeff@freemedsoftware.com> - 0.7.1-1fc1
  - v0.7.1 release

* Fri May 28 2004 Jeff Buchbinder <jeff@freemedsoftware.com> - 0.7.0-1fc1
  - v0.7.0 release

* Fri Apr 16 2004 Jeff Buchbinder <jeff@freemedsoftware.com> - 0.7.0b3-1fc1
  - Initial Fedora Core 1 packaging (apologies to RH9 users)

