#!/usr/bin/perl
# $Id: mk.pl 30 2011-11-18 08:06:02Z yuleivsc@gmail.com $
# ΢����� for Joomla 1.7 
# ���������ڽ�΢������ĸ���Դ���������ɿɰ�װ��ZIP�ļ�

use strict;

use File::Path qw(make_path remove_tree);
use File::Copy;
use Archive::Zip qw( :ERROR_CODES :CONSTANTS );

my $version = '0.8.5'; #�汾��

my $output = 'output'; #���Ŀ¼

# �������ɿյ����Ŀ¼
remove_tree($output);
make_path($output);

# �����Ŀ¼���ļ�����
my $dirs = 0;
my $files = 0;

# ��������Դ����
processdir ( 'com_weibo', $output.'/com_weibo' );
processdir ( 'mod_weibologin/', $output.'/mod_weibologin/' );
processdir ( 'plg_authentication_weibo/', $output.'/plg_authentication_weibo/' );
processdir ( 'plg_content_weibo/', $output.'/plg_content_weibo/' );

# ��������ĳ�����
chdir ( $output );

my $zip = Archive::Zip->new();
$zip->addTree( 'mod_weibologin', 'mod_weibologin' );
unless ( $zip->writeToFileNamed('mod_weibologin.zip') == AZ_OK ) {
    die 'write error';
}
move('mod_weibologin.zip', 'com_weibo/admin/mod_weibologin.zip');

$zip = Archive::Zip->new();
$zip->addTree( 'plg_authentication_weibo', 'plg_authentication_weibo' );
unless ( $zip->writeToFileNamed('plg_authentication_weibo.zip') == AZ_OK ) {
    die 'write error';
}
move('plg_authentication_weibo.zip', 'com_weibo/admin/plg_authentication_weibo.zip');

$zip = Archive::Zip->new();
$zip->addTree( 'plg_content_weibo', 'plg_content_weibo' );
unless ( $zip->writeToFileNamed('plg_content_weibo.zip') == AZ_OK ) {
    die 'write error';
}
move('plg_content_weibo.zip', 'com_weibo/admin/plg_content_weibo.zip');

# �������յĴ���ļ�
$zip = Archive::Zip->new();
$zip->addTree( 'com_weibo', 'com_weibo' );
unless ( $zip->writeToFileNamed('com_weibo.zip') == AZ_OK ) {
    die 'write error';
}

sub processdir
{
    (my $entry, my $outputdir) = @_;
    unless ( -d $outputdir ) {
        eval { make_path($outputdir) };
        if ($@) {
            print "Couldn't create $outputdir: $@";
        }
    }
    opendir ( DIR, $entry );
    my @allfiles = grep { /^[^.]/ } readdir(DIR);
    my @dirs = grep { -d "$entry/$_" } @allfiles;
    my @files = grep { ! -d "$entry/$_" } @allfiles;
    close ( DIR );

    $dirs ++;
    print "$dirs Process dir $entry,  $outputdir\n";
    foreach ( @dirs ) {
        processdir ( $entry.'/'.$_ , $outputdir .'/'.$_);
    }

    
    foreach ( @files ) {
        processfile ( $entry, $outputdir, $_ );
    }
    
}

sub processfile 
{
    (my $entry, my $outputdir, my $filename) = @_;


    $files ++;
    my $inname = "$entry/$filename";
    my $outname = "$outputdir/$filename";
    if ( $filename =~ /png$/i ) {
    	print "$files Process file copy $filename ... \n";
    	print "     copy \"$inname\" \"$outname\" \n";
	copy( $inname, $outname);
    } elsif ( $filename =~ /\~$/i ) {
    	print "$files skip $filename ... \n";
    } else {
    	print "$files Process file sed $filename ... \n";
    	print "     sed s/%VERSION%/$version/g \"$inname\" \"$outname\" \n";
	`sed s/%VERSION%/$version/g "$inname" > "$outname" `;
    }
}
