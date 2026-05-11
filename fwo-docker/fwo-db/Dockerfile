# =============================================================================
# fwo-db - MySQL 4.1.22 with FWO-specific configuration baked in
# =============================================================================
# Inherits from Vettabase's pre-built MySQL 4.1.22 image and adds the
# configuration tweaks we discovered during the v8 downgrade investigation.
#
# Why these tweaks:
#   - skip-symbolic-links: original FWO dump has DATA DIRECTORY=/var/lib/mysql/...
#     clauses on charinv_* tables (prod used hot-swap mounts). This image's
#     datadir is /usr/local/mysql/data, so honoring those clauses fails.
#     skip-symbolic-links makes mysqld silently ignore the clauses and put
#     the tables in the default datadir.
#   - old-passwords: zoneserver/wctrlr/authsys are linked against
#     libmysqlclient.so.10 from the MySQL 4.0/4.1 era, which only speaks the
#     16-byte (old-style) password hash protocol. New-style 41-byte hashes
#     would fail authentication.
#   - log: enables MySQL general query log at /tmp/mysql_general.log.
#     Useful for debugging what queries the FWO binaries are running.
#
# The /var/lib/mysql symlink is defensive — in case any code path tries to
# write there, it transparently redirects to the real datadir.
# =============================================================================
FROM vettadock/mysql-old:4.1

RUN printf '[mysqld]\nskip-symbolic-links\nold-passwords\nlog=/tmp/mysql_general.log\n' > /etc/my.cnf \
    && ln -s /usr/local/mysql/data /var/lib/mysql
