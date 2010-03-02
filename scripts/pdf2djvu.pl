#!/usr/bin/perl
# $Id$
#
# Authors:
#      Jeff Buchbinder <jeff@freemedsoftware.org>
#
# FreeMED Electronic Medical Record and Practice Management System
# Copyright (C) 1999-2010 FreeMED Software Foundation
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

# Directory for temporary image and DjVu files
# All .djvu and .pmb files will be deleted from this directory!
$temp_folder = "/tmp";

unlink <$temp_folder/*.pbm>;

foreach $file_name (@ARGV){
   print "$file_name -> ";
   $error = 0;
   if (system("gs -q -dNOPAUSE -dBATCH -r300 -sDEVICE=pbmraw -sOutputFile=$temp_folder/%d.pbm \"$file_name\"") ){
      $error = 1;
   }
   $i = 0;
   while (defined ($temp_image = glob("$temp_folder/*.pbm")) ){
      ($temp_djvu = $temp_image) =~ s/pbm$/djvu/;
      #You can insert options -clean, -loose or -clean -loose here:
      system ("c44 $temp_image $temp_djvu");
      unlink ($temp_image);
      $temp_djvu =~ s%.*/%%;
      $all_djvu_files[$i++] = $temp_djvu;
   }
   $last_page = $i;
   if ($last_page >= 1){
      @sort_all_djvu_files = sort { $a <=> $b } @all_djvu_files;
      foreach $temp_djvu (@sort_all_djvu_files){
         $param_files .= "$temp_folder/$temp_djvu ";
      }
      if ($error){
         $file_name =~ s/\.pdf$/(1-$last_page).djvu/i;
      }else{
         $file_name =~ s/pdf$/djvu/i;
      }
      system ("djvm -c \"$file_name\" $param_files");
      print "$file_name\n";
      unlink <$temp_folder/*.djvu>;
      undef $param_files;
      undef @all_djvu_files;
   }else{
      print "no pages converted\n";
   }
}
