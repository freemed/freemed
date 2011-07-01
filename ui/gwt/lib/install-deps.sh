#!/bin/bash
#
# $Id$
#
# Authors:
#      Jeff Buchbinder <jeff@freemedsoftware.org>
#
# FreeMED Electronic Medical Record and Practice Management System
# Copyright (C) 1999-2011 FreeMED Software Foundation
#
# This program is free software; you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation; either version 2 of the License, or
# (at your option) any later version.
#
# This program is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License
# along with this program; if not, write to the Free Software
# Foundation, Inc., 675 Mass Ave, Cambridge, MA 02139, USA.

mvn install:install-file -DgroupId=com.googlecode.auroris -DartifactId=ColorPicker-GWT -Dversion=2.1 -Dpackaging=jar -Dfile=ColorPicker-GWT-2.1.jar

mvn install:install-file -DgroupId=com.bouwkamp.gwt -DartifactId=bouwkamp-gwt -Dversion=1.3.1 -Dpackaging=jar -Dfile=com.bouwkamp.gwt.jar

mvn install:install-file -DgroupId=com.googlecode.gwt-html-editor -DartifactId=editor -Dversion=0.1.4 -Dpackaging=jar -Dfile=editor-0.1.4.jar

mvn install:install-file -DgroupId=eu.future.earth.gwt -DartifactId=ftr-gwt-date-emulation -Dversion=1.2.0 -Dpackaging=jar -Dfile=ftr-gwt-date-emulation-1.2.0.jar

mvn install:install-file -DgroupId=eu.future.earth.gwt -DartifactId=ftr-gwt-library-date -Dversion=1.2.0 -Dpackaging=jar -Dfile=ftr-gwt-library-date-1.2.0.jar

mvn install:install-file -DgroupId=eu.future.earth.gwt -DartifactId=ftr-gwt-library-extras -Dversion=0.9.9 -Dpackaging=jar -Dfile=ftr-gwt-library-extras-0.9.9.jar

mvn install:install-file -DgroupId=com.google.gwt -DartifactId=gchart -Dversion=2.7 -Dpackaging=jar -Dfile=gchart-2.7.jar

mvn install:install-file -DgroupId=pl.rmalinowski.gwt2swf -DartifactId=gwt2swf -Dversion=0.6.0 -Dpackaging=jar -Dfile=gwt2swf-0.6.0.jar

mvn install:install-file -DgroupId=com.google.code.gwt-dnd -DartifactId=gwt-dnd -Dversion=3.0.1 -Dpackaging=jar -Dfile=gwt-dnd-3.0.1.jar

mvn install:install-file -DgroupId=com.google.gwt -DartifactId=gwt-incubator -Dversion=20100204-r1747 -Dpackaging=jar -Dfile=gwt-incubator-20100204-r1747.jar

mvn install:install-file -DgroupId=org.cobogw.gwt -DartifactId=org.cobogw.gwt -Dversion=1.3 -Dpackaging=jar -Dfile=org.cobogw.gwt-1.3.jar

mvn install:install-file -DgroupId=com.google.code.gwt-log -DartifactId=gwt-log -Dversion=3.1.3 -Dpackaging=jar -Dfile=gwt-log-3.1.3.jar

