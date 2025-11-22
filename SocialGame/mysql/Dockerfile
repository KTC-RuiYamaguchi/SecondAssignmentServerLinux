FROM mysql:8.0.36-debian

# MySQL APT リポジトリを無効化（鍵期限切れ回避）
RUN rm -f /etc/apt/sources.list.d/mysql.list

RUN apt-get update && \
    apt-get install -y locales && \
    sed -i 's/# ja_JP.UTF-8 UTF-8/ja_JP.UTF-8 UTF-8/' /etc/locale.gen && \
    locale-gen

ENV LANG ja_JP.UTF-8
ENV LC_ALL ja_JP.UTF-8
ENV TZ Asia/Tokyo

COPY ./my.cnf /etc/mysql/conf.d/my.cnf

# ❗ initialize.sql を正しいフォルダにコピーする
COPY ./initialize.sql /docker-entrypoint-initdb.d/
