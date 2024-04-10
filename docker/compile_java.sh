#!/bin/bash

# Guard to ensure running bash.
if ! [ -n "$BASH_VERSION" ];then
    echo "this is not bash, calling self with bash...."
    SCRIPT=$(readlink -f "$0")
    /bin/bash $SCRIPT
    exit
fi

# This script is responsible for compiling the java source code into a jar

JAR_NAME="scheduler.jar"
JAR_PATH=/root/scheduler


SCRIPT=$(readlink -f "$0")
SCRIPTPATH=$(dirname "$SCRIPT") 


cd $SCRIPTPATH/../src

# Create the manifest file for the jar.
echo "Class-Path: libs/google-gson-2.2.4/gson-2.2.4.jar" > manifest.txt
echo "Main-Class: Main" >> manifest.txt

# Compile the program
# we need to specify classpath in order to add the google gson lib
javac -classpath ".:/root/scheduler/src/libs/google-gson-2.2.4/gson-2.2.4.jar" Main.java

# Create the jar compilation of the program.
echo "building jar..."
jar cfmv $JAR_NAME manifest.txt *.class
echo "done"

# Remove the copies of the files we just put inside the jar
rm manifest.txt
rm -rf *.class

# Move the jar to where we want it.
mv $JAR_NAME $JAR_PATH/.
cp -rf libs $JAR_PATH/.


