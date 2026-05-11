# =============================================================================
# FWO Game Server Container
# Build context: root fwo-docker/ folder
# =============================================================================

FROM debian:jessie

RUN dpkg --add-architecture i386

RUN echo "deb http://archive.debian.org/debian jessie main contrib non-free" > /etc/apt/sources.list && \
    echo "deb http://archive.debian.org/debian-security jessie/updates main" >> /etc/apt/sources.list

RUN apt-get update -o Acquire::Check-Valid-Until=false && \
    apt-get install -y --force-yes \
        supervisor \
        libmysqlclient18 \
        mysql-client \
        apache2 \
        libapache2-mod-php5 \
        php5-mysql \
        perl \
        libdbi-perl \
        libdbd-mysql-perl \
        procps \
        net-tools \
        netcat \
        mailutils \
        cron \
        lib32gcc1 \
        lib32stdc++6 \
        zlib1g:i386 \
    && rm -rf /var/lib/apt/lists/*

RUN mkdir -p \
    /FWO13/authsys \
    /FWO13/wctrlr \
    /FWO13/zoneserver/Scenes \
    /FWO13/zoneserver/DBScripts \
    /FWO13/logging \
    /FWO13/script \
    /FWO13/scripts

# Copy old 32-bit libraries
COPY server-files/libs/libmysqlclient.so.10      /lib32/libmysqlclient.so.10
COPY server-files/libs/libstdc++-libc6.2-2.so.3  /lib32/libstdc++-libc6.2-2.so.3
COPY server-files/libs/libssl.so.0.9.6           /lib32/libssl.so.0.9.6
COPY server-files/libs/libcrypto.so.0.9.6        /lib32/libcrypto.so.0.9.6
COPY server-files/libs/libword_count.so          /lib32/libword_count.so
COPY server-files/libs/libwrite_to_file.so       /lib32/libwrite_to_file.so
COPY server-files/libs/libearth_distance.so      /lib32/libearth_distance.so
COPY server-files/libs/libhello.so               /lib32/libhello.so
COPY server-files/libs/liblongest_word.so        /lib32/liblongest_word.so
RUN ldconfig

# Copy patched config files
COPY fwo-server/configs/AS.conf   /FWO13/authsys/AS.conf
COPY fwo-server/configs/WC.conf   /FWO13/wctrlr/WC.conf
COPY fwo-server/configs/ZS.conf   /FWO13/zoneserver/ZS.conf

COPY fwo-server/supervisord/supervisord.conf /etc/supervisor/conf.d/fwo.conf

COPY fwo-server/scripts/start-fwo.sh      /start-fwo.sh
COPY fwo-server/scripts/wait-for-db.sh    /wait-for-db.sh
COPY fwo-server/scripts/run-daemon.sh     /FWO13/scripts/run-daemon.sh
RUN sed -i 's/\r//g' /start-fwo.sh /wait-for-db.sh /FWO13/scripts/run-daemon.sh && \
    chmod +x /start-fwo.sh /wait-for-db.sh /FWO13/scripts/run-daemon.sh

COPY fwo-server/configs/apache-dragon.conf /etc/apache2/sites-available/dragon.conf
RUN a2ensite dragon && a2enmod rewrite php5

RUN mkdir -p /var/log && \
    touch /var/log/script.log \
          /var/log/chat.log \
          /var/log/zs_crash.log

EXPOSE 7778 8888 9999 9998 80

ENTRYPOINT ["/start-fwo.sh"]
