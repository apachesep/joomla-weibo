#!/usr/bin/perl
# $Id: mk.pl 30 2011-11-18 08:06:02Z yuleivsc@gmail.com $
# 微博插件 for Joomla 1.7 
# 本程序用于将微博插件的各个源代码制作成可安装的ZIP文件

use strict;

use File::Path qw(make_path remove_tree);
use File::Copy;
use Archive::Zip qw( :ERROR_CODES :CONSTANTS );

my $version = '0.8.5'; #版本号

my $output = 'output'; #输出目录

# 清理并生成空的输出目录
remove_tree($output);
make_path($output);

# 处理的目录及文件记数
my $dirs = 0;
my $files = 0;

# 处理所有源代码
processdir ( 'com_weibo', $output.'/com_weibo' );
processdir ( 'mod_weibologin/', $output.'/mod_weibologin/' );
processdir ( 'plg_authentication_weibo/', $output.'/plg_authentication_weibo/' );
processdir ( 'plg_content_weibo/', $output.'/plg_content_weibo/' );

# 将处理完的程序打包
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

# 生成最终的打包文件
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
