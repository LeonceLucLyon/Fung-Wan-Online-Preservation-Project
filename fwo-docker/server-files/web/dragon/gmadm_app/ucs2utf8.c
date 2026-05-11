#include <stdio.h>
#include <getopt.h>
#define BUF_SIZE 2048
#define ENUF(msg, value) { perror(msg); exit(value); }

int UCS2toUTF8(unsigned char, unsigned char, unsigned char[]);

void print_usage(unsigned char * program_name)
{
	fprintf(stderr, "Usage: %s [OPTION]... <FILE>
  -b, --begin==NUMBER\t\tprint from line NUMBER. NUMBER is larger than 0.
  -l, --lines==NUMBER\t\tprint number of lines. NUMBER is larger than 0.
  -n, --line-number\t\tprint line number.
  -c, --line-count\t\tprint the newline counts.\n", program_name);
}

int main(int argc, char ** argv)
{
	FILE * fp;
	unsigned char * filename = NULL;
	unsigned char ch_ucs2[2];
	unsigned char ch_utf8[4];
	unsigned char str_utf8[BUF_SIZE];
	int str_utf8_limit = 3;
	int first_line = 1;
	int last_line = 0;
	int line_count = 0;
	char is_get_count = 0;
	char is_show_line_num = 0;
	int digit_optind = 0;
	int ch_len;

	strcpy(str_utf8, "");

	while(1)
	{
		int this_option_optind = optind ? optind : 1;
		int option_index = 0;
		static struct option long_options[] = {
			{"begin", 1, 0, 'b'},
			{"lines", 1, 0, 'l'},
			{"line-number", 1, 0, 'n'},
			{"line-count", 0, 0, 'c'},
			{0, 0, 0, 0}
		};
		int c = getopt_long (argc, argv, "b:l:nc", long_options, &option_index);
		if (c == -1) break;

		switch(c)
		{
			case 'b':
				first_line = atoi(optarg);
				if(first_line < 1)
				{
					fprintf(stderr, "--begin=%s is wrong.\n", optarg);
					print_usage(argv[0]);
					exit(1);
				}
				break;
			case 'l':
				last_line = first_line + atoi(optarg) - 1;
				if(last_line < 1)
				{
					fprintf(stderr, "--lines=%s is wrong.\n", optarg);
					print_usage(argv[0]);
					exit(1);
				}
				break;
			case 'c':
				is_get_count = 1;
				break;
			case 'n':
				is_show_line_num = 1;
				break;
		}
	}
	while (optind < argc)
		filename = argv[optind++];

	if(filename == NULL)
	{
		fprintf(stderr, "Missing input file to read.\n");
		print_usage(argv[0]);
		exit(1);
	}
	
	fp = fopen(filename, "r");
	if(!fp)
	{
		fprintf(stderr, "Failed to open input file, %s.\n", filename);
		exit(2);
	}

	while(fread(ch_ucs2, 1, 2, fp) == 2)
	{
		ch_len = UCS2toUTF8(ch_ucs2[1], ch_ucs2[0], ch_utf8);
		str_utf8_limit += ch_len;

		strncat(str_utf8, ch_utf8, ch_len);

		if(ch_utf8[0] == 10 && ch_utf8[1] == 0)
		{
			++line_count;
			if(line_count >= first_line)
			{
				if(!is_get_count)
				{
					if(is_show_line_num)
						printf("%d. ", line_count);
					printf("%s", str_utf8);
				}
				if(line_count == last_line)
				{
					strcpy(str_utf8, "");
					break;
				}
			}
			strcpy(str_utf8, "");
			str_utf8_limit = 3;
		}
		else if(str_utf8_limit >= BUF_SIZE)
		{
			if(line_count >= first_line)
				printf("%s", str_utf8);
			strcpy(str_utf8, "");
			str_utf8_limit = 3;
		}
	}
	if(is_get_count)
		printf("%d\n", line_count);
	else if(line_count >= first_line)
	       	printf("%s", str_utf8);

	fclose(fp);
}

int UCS2toUTF8 (unsigned char one, unsigned char two, unsigned char * chr)
{
	unsigned int ch = (one * 256) + two;
	if (ch <= 127) {
		chr[0] = ch;
		chr[1] = 0;
		chr[2] = 0;
		return 1;
	} else if (ch <= 2047) {
		chr[0] = 192 | (ch >> 6);
		chr[1] = 128 | (ch & 63);
		chr[2] = 0;
		return 2;
	} else if (ch <= 65535) {
		chr[0] = 224 | (ch >> 12);
		chr[1] = 128 | ((ch >> 6) & 63);
		chr[2] = 128 | (ch & 63);
		return 3;
	} else {
		return 0;
	}
}



