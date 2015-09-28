#!/bin/bash
cd src
export CLASSPATH=$CLASSPATH:./libs//google-gson-2.2.4/*
javac Main.java
java Main
rm *.class
